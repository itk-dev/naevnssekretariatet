<?php

namespace App\Service\OS2Forms\SubmissionManager;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\HearingPostAttachment;
use App\Entity\HearingPostRequest;
use App\Entity\HearingPostResponse;
use App\Entity\User;
use App\Exception\WebformSubmissionException;
use App\Repository\HearingPostRepository;
use App\Repository\PartyRepository;
use App\Service\DocumentUploader;
use App\Service\MailTemplateHelper;
use App\Service\OS2Forms\SubmissionNormalizers\HearingResponseSubmissionNormalizer;
use App\Service\OS2Forms\SubmissionNormalizers\SubmissionNormalizerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class HearingResponseManager
{
    public function __construct(private DocumentUploader $documentUploader, private EntityManagerInterface $entityManager, private HearingPostRepository $hearingPostRepository, private HearingResponseSubmissionNormalizer $normalizer, private MailTemplateHelper $mailTemplateHelper, private PartyRepository $partyRepository, private TranslatorInterface $translator)
    {
    }

    public function createHearingResponseFromSubmissionData(string $sender, array $submissionData)
    {
        $normalizedData = [];
        foreach ($this->getNormalizers() as $normalizer) {
            assert($normalizer instanceof SubmissionNormalizerInterface);
            $normalizedData += $normalizer->normalizeSubmissionData($sender, $submissionData);
        }

        return $this->createHearingResponseFromNormalizedSubmissionData($normalizedData);
    }

    private function getNormalizers(): array
    {
        return [
            $this->normalizer,
        ];
    }

    private function createHearingResponseFromNormalizedSubmissionData(array $normalizedData): HearingPostResponse
    {
        // Ensure that sender is allowed to create hearing response
        $case = $normalizedData['case'];
        assert($case instanceof CaseEntity);

        $sender = $this->partyRepository->findPartyByCaseIdAndIdentification($case->getId(), $normalizedData['identifier']);

        if (null === $sender) {
            $message = sprintf('Could not find party with name %s, identifier %s on case %s', $normalizedData['name'], $normalizedData['identifier'], $case->getCaseNumber());
            throw new WebformSubmissionException($message);
        }

        // Ensure that sender is allowed to create hearing response at the moment.
        $hearingPost = $this->hearingPostRepository->findBy(['hearing' => $case->getHearing()], ['createdAt' => 'DESC']);
        if ($hearingPost) {
            $mostRecentPost = reset($hearingPost);

            if (!$mostRecentPost instanceof HearingPostRequest) {
                $message = 'Last hearing response is not a HearingPostRequest.';
                throw new WebformSubmissionException($message);
            }

            if (!$mostRecentPost->getForwardedOn()) {
                $message = 'Last hearing request has not been forwarded.';
                throw new WebformSubmissionException($message);
            }

            if ($mostRecentPost->getRecipient() !== $sender) {
                $message = sprintf('Hearing response sender (%s) is not recipient (%s) of latest hearing post request.', $sender->getName(), $mostRecentPost->getRecipient()->getName());
                throw new WebformSubmissionException($message);
            }
        }

        // Create hearing response and set properties
        $hearingResponse = new HearingPostResponse();

        $hearingResponse->setResponse($normalizedData['response']);

        $hearingResponse->setHearing($case->getHearing());
        $hearingResponse->setSender($sender);

        // Handle document
        $template = $case->getBoard()->getHearingPostResponseTemplate();

        $fileName = $this->mailTemplateHelper->renderMailTemplate($template, $hearingResponse);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['name' => 'OS2Forms']);
        $today = new \DateTime('today');
        $documentName = $this->translator->trans('Hearing post response by {sender} on {date}', ['sender' => $sender->getName(), 'date' => $today->format('d/m/Y')], 'case');
        // The document type is translated in templates/translations/mail_template.html.twig
        $documentType = 'Hearing post response';
        $document = $this->documentUploader->createDocumentFromPath($fileName, $documentName, $documentType, $user);

        $this->entityManager->persist($document);

        $caseDocumentRelation = new CaseDocumentRelation();

        $caseDocumentRelation
            ->setCase($case)
            ->setDocument($document)
        ;

        $this->entityManager->persist($caseDocumentRelation);

        $hearingResponse->setDocument($document);

        // Handle attachments
        if ($normalizedData['documents']) {
            foreach ($normalizedData['documents'] as $document) {
                $caseDocumentRelation = new CaseDocumentRelation();

                $caseDocumentRelation
                    ->setCase($case)
                    ->setDocument($document)
                ;

                $this->entityManager->persist($caseDocumentRelation);

                $hearingPostAttachment = new HearingPostAttachment();
                $hearingPostAttachment->setDocument($document);
                $hearingPostAttachment->setHearingPost($hearingResponse);

                $this->entityManager->persist($hearingPostAttachment);

                $hearingResponse->addAttachment($hearingPostAttachment);
            }
        }

        return $hearingResponse;
    }
}
