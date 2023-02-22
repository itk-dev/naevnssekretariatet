<?php

namespace App\Service;

use App\Entity\Board;
use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\CasePartyRelation;
use App\Entity\DigitalPost;
use App\Entity\Embeddable\Address;
use App\Entity\LogEntry;
use App\Entity\Reminder;
use App\Repository\BoardRepository;
use App\Repository\CaseEntityRepository;
use App\Repository\DigitalPostRepository;
use App\Repository\MunicipalityRepository;
use App\Service\OS2Forms\SubmissionManager\CaseSubmissionManagerInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class CaseManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly BoardRepository $boardRepository, private readonly CaseEntityRepository $caseRepository, private readonly EntityManagerInterface $entityManager, private readonly LockFactory $lockFactory, private readonly MunicipalityRepository $municipalityRepository, private readonly PropertyAccessorInterface $propertyAccessor, private readonly WorkflowService $workflowService, private readonly DocumentUploader $documentUploader)
    {
    }

    public function generateCaseNumber(): string
    {
        $case = $this->caseRepository->findLatestCase();

        // Get current year
        $date = new \DateTime();
        $currentYear = $date->format('y');

        // Check if first case
        if (null === $case) {
            return $currentYear.'-000001';
        }

        // Get the year latest case is from
        $latestCaseYear = $case->getCreatedAt()->format('y');

        // Check if we have entered a new year
        if ($currentYear !== $latestCaseYear) {
            return $currentYear.'-000001';
        }

        return $latestCaseYear.'-'.$this->getIncrementedCaseCounter($case->getCaseNumber());
    }

    public function getIncrementedCaseCounter(string $caseNumber): string
    {
        // Find dash, get counter and increment it
        $positionOfDash = strpos($caseNumber, '-');
        $counter = (int) substr($caseNumber, $positionOfDash + 1);
        $incrementedCounter = $counter + 1;

        return str_pad(strval($incrementedCounter), 6, '0', STR_PAD_LEFT);
    }

    public function newCase(CaseEntity $caseEntity, Board $board): CaseEntity
    {
        $lock = $this->lockFactory->createLock('case-generation');

        $lock->acquire(true);

        $caseEntity->setCaseNumber(
            $this->generateCaseNumber()
        );
        $caseEntity->setBoard($board);
        $caseEntity->setMunicipality($board->getMunicipality());

        $workflow = $this->workflowService->getWorkflowForCase($caseEntity);
        $workflow->getMarking($caseEntity);

        // Set deadlines via board default deadlines
        $hearingModifier = sprintf('+%s days', $board->getFinishHearingDeadlineDefault());
        $processModifier = sprintf('+%s days', $board->getFinishProcessingDeadlineDefault());

        $caseEntity->setFinishHearingDeadline((new \DateTime('today'))->modify($hearingModifier));
        $caseEntity->setFinishProcessingDeadline((new \DateTime('today'))->modify($processModifier));

        $this->entityManager->persist($caseEntity);
        $this->entityManager->flush();

        $lock->release();

        return $caseEntity;
    }

    public function updateDeadlineBooleans($isDryRun)
    {
        // We do this in batches as there might be a large number of cases
        // @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.9/reference/batch-processing.html#iterating-results
        $batchSize = 20;
        $caseCounter = 1;
        $query = $this->caseRepository->createQueryBuilder('c')->getQuery();

        $today = new DateTime('today');

        $this->logger->info('Today: '.$today->format('d/m/Y'));

        foreach ($query->toIterable() as $case) {
            assert($case instanceof CaseEntity);
            $hearingDeadline = $case->getFinishHearingDeadline();
            $processingDeadline = $case->getFinishProcessingDeadline();
            $hearingResponseDeadline = $case->getHearingResponseDeadline();

            if ($hearingResponseDeadline && $hearingResponseDeadline < $today) {
                $this->logger->info('Changing case '.$case->getCaseNumber().' with hearing response deadline: '.$hearingResponseDeadline->format('d/m/Y'));

                if (!$isDryRun) {
                    $case->setHasReachedHearingResponseDeadline(true);
                }
            }

            if ($hearingDeadline < $today) {
                $this->logger->info('Changing case '.$case->getCaseNumber().' with hearing deadline: '.$hearingDeadline->format('d/m/Y'));

                if (!$isDryRun) {
                    $case->setHasReachedHearingDeadline(true);
                }

                if ($processingDeadline < $today) {
                    $this->logger->info('Changing case '.$case->getCaseNumber().' with processing deadline: '.$hearingDeadline->format('d/m/Y'));

                    if (!$isDryRun) {
                        $case->setHasReachedProcessingDeadline(true);
                    }
                }
            }
            ++$caseCounter;

            if (($caseCounter % $batchSize) === 0 && !$isDryRun) {
                $this->entityManager->flush(); // Executes all updates.
                $this->entityManager->clear(); // Detaches all objects from Doctrine!
            }
        }

        if (!$isDryRun) {
            $this->entityManager->flush();
        }
    }

    public function getCaseIdentificationValues(CaseEntity $case, string $addressProperty, string $nameProperty, string $addressProtectionProperty = null): array
    {
        /** @var Address $address */
        $address = $this->propertyAccessor->getValue($case, $addressProperty);
        $name = $this->propertyAccessor->getValue($case, $nameProperty);

        $data = [];
        $data['name'] = $name;
        $data['street'] = $address->getStreet();
        $data['number'] = $address->getNumber();
        $data['floor'] = $address->getFloor() ?? '';
        $data['side'] = $address->getSide() ?? '';
        $data['postalCode'] = $address->getPostalCode();
        $data['city'] = $address->getCity();

        if ($addressProtectionProperty) {
            $data['isUnderAddressProtection'] = $this->propertyAccessor->getValue($case, $addressProtectionProperty);
        }

        return $data;
    }

    public function handleOS2FormsCaseSubmission(string $sender, array $submissionData, CaseSubmissionManagerInterface $manager)
    {
        [$case, $board, $documents] = $manager->createCaseFromSubmissionData($sender, $submissionData);

        assert($case instanceof CaseEntity);
        assert($board instanceof Board);

        $case = $this->newCase($case, $board);
        if (is_array($documents)) {
            foreach ($documents as $document) {
                $caseDocumentRelation = new CaseDocumentRelation();

                $caseDocumentRelation
                    ->setCase($case)
                    ->setDocument($document)
                ;

                $this->entityManager->persist($caseDocumentRelation);
            }
        }

        // Cases created via OS2Forms get a default received at (today).
        $case->setReceivedAt(new DateTime('today'));

        $this->entityManager->flush();
    }

    public function deleteCase(CaseEntity $case)
    {
        /** @var DigitalPostRepository $digitalPostRepostiory */
        $digitalPostRepository = $this->entityManager->getRepository(DigitalPost::class);

        // Remove notes
        $notes = $case->getNotes();

        foreach ($notes as $note) {
            $this->entityManager->remove($note);
        }

        // Remove Agenda stuff - but keep party till later.
        $agendaCaseItems = $case->getAgendaCaseItems();

        foreach ($agendaCaseItems as $agendaCaseItem) {
            $digitalPosts = $digitalPostRepository->findBy(['entityId' => $agendaCaseItem]);

            foreach ($digitalPosts as $digitalPost) {
                // Inspection letters cannot have attachments so no need to check for those.
                foreach ($digitalPost->getRecipients() as $recipient) {
                    $this->entityManager->remove($recipient);
                }
                $this->entityManager->remove($digitalPost);
            }

            foreach ($agendaCaseItem->getInspectionLetters() as $inspectionLetter) {
                $document = $inspectionLetter->getDocument();
                $this->entityManager->remove($document);

                $agendaCaseItem->removeInspectionLetter($inspectionLetter);
            }

            $this->entityManager->remove($agendaCaseItem);
        }

        // Remove hearing stuff - but keep party till later.
        foreach ($case->getHearing()->getHearingPosts() as $hearingPost) {
            foreach ($hearingPost->getAttachments() as $attachment) {
                $this->entityManager->remove($attachment);
            }

            $hearingPost->getHearing()->removeHearingPost($hearingPost);

            $this->entityManager->remove($hearingPost);
        }

        // Remove Digital Post stuff - but keep party till later.
        $digitalPosts = $digitalPostRepository->findBy(['entityId' => $case]);

        foreach ($digitalPosts as $digitalPost) {
            foreach ($digitalPost->getAttachments() as $attachment) {
                $attachment->setDocument(null);
                $this->entityManager->remove($attachment);
            }
            foreach ($digitalPost->getRecipients() as $recipient) {
                $this->entityManager->remove($recipient);
            }
            $this->entityManager->remove($digitalPost);
        }

        // Remove Decision stuff
        foreach ($case->getDecisions() as $decision) {
            foreach ($decision->getAttachments() as $attachment) {
                $attachment->setDocument(null);
                $this->entityManager->remove($attachment);
            }

            $this->entityManager->remove($decision);
        }

        // Remove document stuff.
        foreach ($case->getCaseDocumentRelation() as $relation) {
            $document = $relation->getDocument();

            $document->removeCaseDocumentRelation($relation);
            $this->entityManager->remove($relation);

            // Check if the document was only related to this case and remove it if so.
            if ($document->getCaseDocumentRelations()->isEmpty()) {
                $this->documentUploader->deleteDocumentFile($document);
                $this->entityManager->remove($document);
            }
        }

        foreach ($case->getCasePartyRelation() as $relation) {
            // Check if the party is only related to this case and remove it if so.
            $party = $relation->getParty();
            $partyRelations = $this->entityManager
                ->getRepository(CasePartyRelation::class)
                ->findBy(['party' => $party])
            ;
            if (1 === count($partyRelations)) {
                $this->entityManager->remove($party);
            }

            $this->entityManager->remove($relation);
        }

        // Remove Reminder stuff
        $reminderRepository = $this->entityManager->getRepository(Reminder::class);
        $reminders = $reminderRepository->findBy(['caseEntity' => $case]);

        foreach ($reminders as $reminder) {
            $this->entityManager->remove($reminder);
        }

        // Remove log entries
        $logEntryRepository = $this->entityManager->getRepository(LogEntry::class);
        $logEntries = $logEntryRepository->findBy(['caseID' => $case]);

        foreach ($logEntries as $logEntry) {
            $this->entityManager->remove($logEntry);
        }

        // Finally remove the case.
        $this->entityManager->remove($case);
        $this->entityManager->flush();
    }
}
