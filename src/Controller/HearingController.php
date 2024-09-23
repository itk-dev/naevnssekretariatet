<?php

namespace App\Controller;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\CaseEvent;
use App\Entity\DigitalPost;
use App\Entity\DigitalPostAttachment;
use App\Entity\Document;
use App\Entity\Hearing;
use App\Entity\HearingBriefing;
use App\Entity\HearingPost;
use App\Entity\HearingPostRequest;
use App\Entity\HearingPostResponse;
use App\Entity\HearingRecipient;
use App\Entity\User;
use App\Exception\HearingException;
use App\Form\HearingFinishType;
use App\Form\HearingPostRequestType;
use App\Form\HearingPostResponseType;
use App\Repository\CaseDocumentRelationRepository;
use App\Repository\DocumentRepository;
use App\Repository\HearingPostRepository;
use App\Service\CaseEventHelper;
use App\Service\DigitalPostHelper;
use App\Service\DocumentUploader;
use App\Service\MailTemplateHelper;
use App\Service\PartyHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/case")
 */
class HearingController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private DocumentUploader $documentUploader, private CaseDocumentRelationRepository $relationRepository, private TranslatorInterface $translator)
    {
    }

    /**
     * @Route("/{id}/hearing", name="case_hearing_index")
     */
    public function index(CaseEntity $case, HearingPostRepository $hearingPostRepository, PartyHelper $partyHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // Check whether there is at least one part for each side
        $relevantParties = $partyHelper->getRelevantPartiesByCase($case);

        // Cases may enter the hearing phase with any one part.
        $hasSufficientParties = count($relevantParties['parties']) + count($relevantParties['counterparties']) > 0;
        $hasCounterparty = count($relevantParties['counterparties']) > 0;
        $hasParty = count($relevantParties['parties']) > 0;

        $hearing = $case->getHearing();

        if (null === $hearing) {
            $hearing = new Hearing();
            $case->setHearing($hearing);
            $hearing->setCaseEntity($case);

            $this->entityManager->persist($hearing);
            $this->entityManager->flush();
        }

        $partyHasSomethingToAdd = ($hasCounterparty && !$hearing->getCounterpartHasNoMoreToAdd())
            || ($hasParty && !$hearing->getPartyHasNoMoreToAdd());

        $form = $this->createForm(HearingFinishType::class, $hearing, ['case' => $case, 'hasParty' => $hasParty, 'hasCounterparty' => $hasCounterparty]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Hearing updated', [], 'case'));

            return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
        }

        $hearingPosts = $hearingPostRepository->findBy(['hearing' => $hearing], ['createdAt' => 'DESC']);

        // Detect whether most recent hearing post has been forwarded or even exists
        $mostRecentPost = reset($hearingPosts);

        $requiresProcessing = $mostRecentPost instanceof HearingPostResponse ? !$mostRecentPost->getApprovedOn() : $mostRecentPost && !$mostRecentPost->getForwardedOn();

        return $this->render('case/hearing/index.html.twig', [
            'case' => $case,
            'hearing' => $hearing,
            'posts' => $hearingPosts,
            'hasCounterparty' => $hasCounterparty,
            'hasParty' => $hasParty,
            'partyHasSomethingToAdd' => $partyHasSomethingToAdd,
            'hasSufficientParties' => $hasSufficientParties,
            'requiresProcessing' => $requiresProcessing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/hearing/start", name="case_hearing_start")
     */
    public function startHearing(CaseEntity $case): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $hearing = $case->getHearing();

        // Mark hearing as started
        $today = new \DateTime('today');
        $hearing->setStartedOn($today);
        $this->entityManager->persist($hearing);
        $this->entityManager->flush();
        $this->addFlash('success', new TranslatableMessage('Hearing started', [], 'case'));

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }

    /**
     * @Route("/{case}/hearing/{hearing}/response/create", name="case_hearing_post_response_create")
     */
    public function hearingPostResponseCreate(CaseEntity $case, DocumentRepository $documentRepository, DocumentUploader $documentUploader, Hearing $hearing, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearing->getFinishedOn()) {
            throw new HearingException();
        }

        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);
        $caseDocuments = $documentRepository->getAvailableDocumentsForCase($case);

        $hearingPost = new HearingPostResponse();

        $form = $this->createForm(HearingPostResponseType::class, $hearingPost, [
            'case_parties' => $availableParties,
            'available_case_documents' => $caseDocuments,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hearingPost->setHearing($hearing);

            // Create new file from template
            $fileName = $mailTemplateHelper->renderMailTemplate($case->getBoard()->getHearingPostResponseTemplate(), $hearingPost);

            $today = new \DateTime('today');
            $documentName = $this->translator->trans('Hearing post response by {sender} on {date}', ['sender' => $hearingPost->getSender()->getName(), 'date' => $today->format('d/m/Y')], 'case');
            $documentType = 'Hearing post response';
            // Create document
            $document = $documentUploader->createDocumentFromPath($fileName, $documentName, $documentType);

            $hearingPost->setDocument($document);
            $hearingPost->setHearing($hearing);

            $hearing->setHasNewHearingPost(true);

            // Create case document relation
            $relation = new CaseDocumentRelation();
            $relation->setCase($case);
            $relation->setDocument($document);

            $this->entityManager->persist($relation);
            $this->entityManager->persist($document);
            $this->entityManager->persist($hearingPost);
            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Hearing post response created', [], 'case'));

            return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId(), 'hearing' => $hearing->getId()]);
        }

        return $this->render('case/hearing/post_create.html.twig', [
            'translated_title' => $this->translator->trans('Create hearing response', [], 'case'),
            'case' => $case,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearing}/request/create", name="case_hearing_post_request_create")
     */
    public function hearingPostRequestCreate(CaseEntity $case, DocumentUploader $documentUploader, DocumentRepository $documentRepository, Hearing $hearing, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearing->getFinishedOn()) {
            throw new HearingException();
        }

        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);

        $mailTemplates = $mailTemplateHelper->getTemplates('hearing');

        $hearingPost = new HearingPostRequest();

        $caseDocuments = $documentRepository->getAvailableDocumentsForCase($case);

        $form = $this->createForm(HearingPostRequestType::class, $hearingPost, [
            'case_parties' => $availableParties,
            'mail_template_choices' => $mailTemplates,
            'available_case_documents' => $caseDocuments,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $customData = [];

            foreach ($form->get('customData') as $customField) {
                $name = $customField->getName();
                $customData[$name] = $customField->getData();
            }

            $hearingPost->setCustomData($customData);

            $hearingPost->setHearing($hearing);

            // Per chosen recipient, create a HearingRecipient and add it to the HearingPostRequest
            // HearingRecipient is then responsible for containing the rendered document.
            foreach ($form->get('recipients')->getData() as $recipient) {
                $hearingRecipient = new HearingRecipient();
                $hearingRecipient->setRecipient($recipient);
                $hearingRecipient->setHearingPostRequest($hearingPost);

                // Create new file from template
                $fileName = $mailTemplateHelper->renderMailTemplate($hearingPost->getTemplate(), $hearingRecipient);

                // Create document
                $document = $documentUploader->createDocumentFromPath($fileName, $hearingPost->getTitle(), 'Hearing');

                $hearingRecipient->setDocument($document);

                // Create case document relation
                $relation = new CaseDocumentRelation();
                $relation->setCase($case);
                $relation->setDocument($document);

                $this->entityManager->persist($relation);
                $this->entityManager->persist($document);
                $this->entityManager->persist($hearingRecipient);

                $hearingPost->addHearingRecipient($hearingRecipient);
            }

            $hearing->setHasNewHearingPost(false);
            $this->entityManager->persist($hearingPost);
            $this->entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('Hearing post request created', [], 'case'));

            if ($hearingPost->shouldSendBriefing()) {
                return $this->redirectToRoute('case_hearing_briefing_create', ['case' => $case->getId(), 'hearing' => $hearing->getId(), 'hearingPost' => $hearingPost->getId()]);
            } else {
                return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId(), 'hearing' => $hearing->getId()]);
            }
        }

        return $this->render('case/hearing/post_create.html.twig', [
            'translated_title' => $this->translator->trans('Create hearing request', [], 'case'),
            'case' => $case,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/show", name="case_hearing_post_show")
     */
    public function hearingPostShow(CaseEntity $case, HearingPost $hearingPost): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        return $this->render('case/hearing/post_show.html.twig', [
            'case' => $case,
            'hearingPost' => $hearingPost,
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/response/edit", name="case_hearing_post_response_edit")
     */
    public function hearingPostResponseEdit(CaseEntity $case, DocumentRepository $documentRepository, DocumentUploader $documentUploader, HearingPost $hearingPost, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearingPost->getHearing()->getFinishedOn()) {
            throw new HearingException();
        }

        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);
        $caseDocuments = $documentRepository->getAvailableDocumentsForCase($case);

        $form = $this->createForm(HearingPostResponseType::class, $hearingPost, [
            'case_parties' => $availableParties,
            'available_case_documents' => $caseDocuments,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Create new file
            $fileName = $mailTemplateHelper->renderMailTemplate($case->getBoard()->getHearingPostResponseTemplate(), $hearingPost);

            // For now, we just overwrite completely
            $documentUploader->replaceFileContent($hearingPost->getDocument(), $fileName);

            // Update Document
            /** @var User $user */
            $user = $this->getUser();
            $today = new \DateTime('today');
            $documentName = $this->translator->trans('Hearing post response by {sender} on {date}', ['sender' => $hearingPost->getSender()->getName(), 'date' => $today->format('d/m/Y')], 'case');
            $hearingPost->getDocument()->setDocumentName($documentName);
            $hearingPost->getDocument()->setUploadedBy($user);
            $hearingPost->getDocument()->setUploadedAt(new \DateTime('now'));

            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Hearing post response updated', [], 'case'));

            return $this->redirectToRoute('case_hearing_post_show', ['case' => $case->getId(), 'hearingPost' => $hearingPost->getId()]);
        }

        return $this->render('case/hearing/post_edit.html.twig', [
            'case' => $case,
            'hearingPost' => $hearingPost,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/request/edit", name="case_hearing_post_request_edit")
     */
    public function hearingPostRequestEdit(CaseEntity $case, DocumentRepository $documentRepository, DocumentUploader $documentUploader, HearingPostRequest $hearingPost, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearingPost->getHearing()->getFinishedOn()) {
            throw new HearingException();
        }

        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);
        $mailTemplates = $mailTemplateHelper->getTemplates('hearing');
        $informMailTemplates = $mailTemplateHelper->getTemplates('briefing');

        $caseDocuments = $documentRepository->getAvailableDocumentsForCase($case);

        $form = $this->createForm(HearingPostRequestType::class, $hearingPost, [
            'case_parties' => $availableParties,
            'available_case_documents' => $caseDocuments,
            'preselects' => array_map(static fn (HearingRecipient $hearingRecipient) => $hearingRecipient->getRecipient(), $hearingPost->getHearingRecipients()->toArray()),
            'mail_template_choices' => $mailTemplates,
        ]);

        $briefing = $hearingPost->getBriefing();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // It is not sufficient to recreate document only if mail template is switched
            $customData = [];

            foreach ($form->get('customData') as $customField) {
                $name = $customField->getName();
                $customData[$name] = $customField->getData();
            }

            $hearingPost->setCustomData($customData);

            // We do it the slow way by simply just remaking the documents
            foreach ($hearingPost->getHearingRecipients() as $hearingRecipient) {
                $document = $hearingRecipient->getDocument();

                $this->removeDocumentFromCase($case, $document);

                $hearingPost->removeHearingRecipient($hearingRecipient);

                $this->entityManager->remove($hearingRecipient);
                $this->entityManager->flush();
            }

            // Per chosen recipient, create a HearingRecipient and add it to the HearingPostRequest
            // HearingRecipient is then responsible for containing the rendered document.
            foreach ($form->get('recipients')->getData() as $recipient) {
                $hearingRecipient = new HearingRecipient();
                $hearingRecipient->setRecipient($recipient);
                $hearingRecipient->setHearingPostRequest($hearingPost);

                // Create new file from template
                $fileName = $mailTemplateHelper->renderMailTemplate($hearingPost->getTemplate(), $hearingRecipient);

                // Create document
                $document = $documentUploader->createDocumentFromPath($fileName, $hearingPost->getTitle(), 'Hearing');

                $hearingRecipient->setDocument($document);

                // Create case document relation
                $relation = new CaseDocumentRelation();
                $relation->setCase($case);
                $relation->setDocument($document);

                $this->entityManager->persist($relation);
                $this->entityManager->persist($document);
                $this->entityManager->persist($hearingRecipient);

                $hearingPost->addHearingRecipient($hearingRecipient);
            }

            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Hearing post request updated', [], 'case'));

            if ($hearingPost->shouldSendBriefing()) {
                if (null === $briefing) {
                    return $this->redirectToRoute('case_hearing_briefing_create', ['case' => $case->getId(), 'hearing' => $hearingPost->getHearing()->getId(), 'hearingPost' => $hearingPost->getId()]);
                } else {
                    return $this->redirectToRoute('case_hearing_briefing_edit', ['case' => $case->getId(), 'hearing' => $hearingPost->getHearing()->getId(), 'hearingPost' => $hearingPost->getId(), 'briefing' => $briefing->getId()]);
                }
            } else {
                if (null != $briefing) {
                    // Necessary due to cascade
                    $hearingPost->setBriefing(null);
                    $briefing->setHearingPostRequest(null);

                    $this->removeBriefing($briefing);
                }

                return $this->redirectToRoute('case_hearing_post_show', ['case' => $case->getId(), 'hearingPost' => $hearingPost->getId()]);
            }
        }

        return $this->render('case/hearing/post_edit.html.twig', [
            'case' => $case,
            'hearingPost' => $hearingPost,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/approve", name="case_hearing_post_approve", methods={"POST"})
     */
    public function hearingPostApprove(CaseEntity $case, HearingPostResponse $hearingPost, CaseEventHelper $caseEventHelper): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearingPost->getHearing()->getFinishedOn()) {
            throw new HearingException();
        }

        // Create case event (sagshændelse)
        $documents = [];
        $documents[] = $hearingPost->getDocument();

        foreach ($hearingPost->getAttachments() as $attachment) {
            $documents[] = $attachment->getDocument();
        }

        $caseEventHelper->createDocumentCaseEvent($case, CaseEvent::SUBJECT_HEARING_CONTRADICTIONS_BRIEFING, [$hearingPost->getSender()], null, [], null, $documents);

        $today = new \DateTime('today');
        $hearingPost->setApprovedOn($today);
        $hearingPost->getHearing()->setHasNewHearingPost(false);

        $this->entityManager->flush();
        $this->addFlash('success', new TranslatableMessage('Hearing post approved', [], 'case'));

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/forward", name="case_hearing_post_forward", methods={"POST"})
     */
    public function hearingPostForward(CaseEntity $case, HearingPostRequest $hearingPost, DigitalPostHelper $digitalPostHelper, CaseEventHelper $caseEventHelper): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearingPost->getHearing()->getFinishedOn()) {
            throw new HearingException();
        }

        // Create DigitalPost attachments without linking them to a specific DigitalPost
        $digitalPostAttachments = [];

        $attachments = $hearingPost->getAttachments();

        foreach ($attachments as $attachment) {
            $digitalPostAttachment = new DigitalPostAttachment();
            $digitalPostAttachment->setDocument($attachment->getDocument());
            $digitalPostAttachments[] = $digitalPostAttachment;
        }

        foreach ($hearingPost->getHearingRecipients() as $hearingRecipient) {
            $digitalPostRecipient = (new DigitalPost\Recipient())
                ->setName($hearingRecipient->getRecipient()->getName())
                ->setIdentifierType($hearingRecipient->getRecipient()->getIdentification()->getType())
                ->setIdentifier($hearingRecipient->getRecipient()->getIdentification()->getIdentifier())
                ->setAddress($hearingRecipient->getRecipient()->getAddress())
            ;

            $clonedAttachments = array_map(static fn (DigitalPostAttachment $attachment) => (new DigitalPostAttachment())->setDigitalPost($attachment->getDigitalPost())->setDocument($attachment->getDocument()), $digitalPostAttachments);

            $digitalPost = $digitalPostHelper->createDigitalPost($hearingRecipient->getDocument(), $hearingPost->getTitle(), get_class($case), $case->getId(), $clonedAttachments, [$digitalPostRecipient]);

            $caseEventHelper->createDigitalPostCaseEvent($case, $digitalPost, $hearingPost->getTitle(), [$hearingRecipient->getRecipient()]);
        }

        // Now handle potential briefings.
        if ($hearingPost->shouldSendBriefing()) {
            $briefing = $hearingPost->getBriefing();
            if (!$briefing) {
                throw new HearingException('Attempting to send empty briefing');
            }

            $briefingRecipients = $briefing->getHearingBriefingRecipients();
            if ($briefingRecipients->isEmpty()) {
                throw new HearingException('Could not find any recipients for briefing');
            }

            foreach ($briefingRecipients as $briefingRecipient) {
                $digitalPostBriefingRecipient = (new DigitalPost\Recipient())
                    ->setName($briefingRecipient->getRecipient()->getName())
                    ->setIdentifierType($briefingRecipient->getRecipient()->getIdentification()->getType())
                    ->setIdentifier($briefingRecipient->getRecipient()->getIdentification()->getIdentifier())
                    ->setAddress($briefingRecipient->getRecipient()->getAddress())
                ;

                $briefingAttachments = $briefingRecipient->getAttachments();
                $digitalPostBriefingAttachments = [];

                foreach ($briefingAttachments as $briefingAttachment) {
                    $digitalPostAttachment = new DigitalPostAttachment();
                    $digitalPostAttachment->setDocument($briefingAttachment);
                    $digitalPostBriefingAttachments[] = $digitalPostAttachment;
                }

                $clonedBriefingAttachments = array_map(static fn (DigitalPostAttachment $attachment) => (new DigitalPostAttachment())->setDigitalPost($attachment->getDigitalPost())->setDocument($attachment->getDocument()), $digitalPostBriefingAttachments);

                $digitalPost = $digitalPostHelper->createDigitalPost($briefingRecipient->getDocument(), $briefing->getTitle(), get_class($case), $case->getId(), $clonedBriefingAttachments, [$digitalPostBriefingRecipient]);

                $caseEventHelper->createDigitalPostCaseEvent($case, $digitalPost, $briefing->getTitle(), [$briefingRecipient->getRecipient()]);
            }
        }

        $today = new \DateTime('today');

        $hearingResponseModifier = sprintf('+%s days', $case->getBoard()->getHearingResponseDeadline());
        $case->setHearingResponseDeadline($today->modify($hearingResponseModifier));

        $hearingPost->setForwardedOn($today);
        $hearingPost->getHearing()->setHasNewHearingPost(false);
        $this->entityManager->flush();
        $this->addFlash('success', new TranslatableMessage('Hearing post forwarded', [], 'case'));

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }

    /**
     * @Route("/{case}/hearing/{hearing}/finish", name="case_hearing_finish")
     */
    public function finishHearing(CaseEntity $case, Hearing $hearing): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // TODO: Consider whether more logic is needed upon finishing a hearing
        $today = new \DateTime('today');
        $hearing->setFinishedOn($today);
        $this->entityManager->flush();
        $this->addFlash('success', new TranslatableMessage('Hearing finished', [], 'case'));

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }

    /**
     * @Route("/{case}/hearing/{hearing}/resume", name="case_hearing_resume")
     */
    public function resumeHearing(CaseEntity $case, Hearing $hearing): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // TODO: Consider whether more logic is needed upon resuming a hearing
        $hearing->setFinishedOn(null);
        $this->entityManager->flush();
        $this->addFlash('success', new TranslatableMessage('Hearing resumed', [], 'case'));

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/delete", name="case_hearing_post_delete")
     */
    public function hearingPostDelete(CaseEntity $case, HearingPost $hearingPost, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$hearingPost->getId(), $request->request->get('_token'))) {
            if ($hearingPost instanceof HearingPostRequest) {
                foreach ($hearingPost->getHearingRecipients() as $hearingRecipient) {
                    $document = $hearingRecipient->getDocument();

                    $this->removeDocumentFromCase($case, $document);

                    $hearingPost->removeHearingRecipient($hearingRecipient);
                    $this->entityManager->remove($hearingRecipient);
                }

                if ($briefing = $hearingPost->getBriefing()) {
                    $this->removeBriefing($briefing);
                }
            } elseif ($hearingPost instanceof HearingPostResponse) {
                // Remove relation between case and document
                $document = $hearingPost->getDocument();

                $this->removeDocumentFromCase($case, $document);

                $hearingPost->setDocument(null);
            } else {
                throw new HearingException(sprintf('Unhandled HearingPost of type %s detected during deletion.', get_class($hearingPost)));
            }

            // Remove new hearing post alert
            $hearingPost->getHearing()->setHasNewHearingPost(false);

            // Remove hearing post and document
            $this->entityManager->remove($hearingPost);
            $this->entityManager->flush();

            $message = match (get_class($hearingPost)) {
                HearingPostResponse::class => new TranslatableMessage('Hearing post response deleted', [], 'case'),
                default => new TranslatableMessage('Hearing post request deleted', [], 'case'),
            };

            $this->addFlash('success', $message);
        }

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }

    public function removeDocumentFromCase(CaseEntity $case, Document $document)
    {
        $relation = $this->relationRepository->findOneBy(['case' => $case, 'document' => $document]);

        if (null !== $relation) {
            $this->entityManager->remove($relation);
            $this->entityManager->flush();
        }

        // Remove file and document
        $this->documentUploader->deleteDocumentFile($document);
        $this->entityManager->remove($document);
    }

    private function removeBriefing(HearingBriefing $briefing)
    {
        $briefingRecipients = $briefing->getHearingBriefingRecipients();
        foreach ($briefingRecipients as $briefingRecipient) {
            $document = $briefingRecipient->getDocument();

            $this->removeDocumentFromCase($briefing->getHearingPostRequest()->getHearing()->getCaseEntity(), $document);
            $this->entityManager->remove($briefingRecipient);
        }

        $this->entityManager->remove($briefing);
        $this->entityManager->flush();
    }
}
