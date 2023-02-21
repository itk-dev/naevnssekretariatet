<?php

namespace App\Controller;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\DigitalPost;
use App\Entity\DigitalPostAttachment;
use App\Entity\Document;
use App\Entity\Hearing;
use App\Entity\HearingPost;
use App\Entity\HearingPostRequest;
use App\Entity\HearingPostResponse;
use App\Entity\User;
use App\Exception\HearingException;
use App\Form\HearingFinishType;
use App\Form\HearingPostRequestType;
use App\Form\HearingPostResponseType;
use App\Repository\CaseDocumentRelationRepository;
use App\Repository\DocumentRepository;
use App\Repository\HearingPostRepository;
use App\Service\DigitalPostHelper;
use App\Service\DocumentUploader;
use App\Service\MailTemplateHelper;
use App\Service\PartyHelper;
use DateTime;
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
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly TranslatorInterface $translator)
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
        $today = new DateTime('today');
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

            // Create new file from template
            $fileName = $mailTemplateHelper->renderMailTemplate($hearingPost->getTemplate(), $hearingPost);

            // Create document
            $document = $documentUploader->createDocumentFromPath($fileName, $hearingPost->getTitle(), 'Hearing');

            $hearingPost->setDocument($document);

            // Create case document relation
            $relation = new CaseDocumentRelation();
            $relation->setCase($case);
            $relation->setDocument($document);

            $hearing->setHasNewHearingPost(false);
            $this->entityManager->persist($relation);
            $this->entityManager->persist($document);
            $this->entityManager->persist($hearingPost);
            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Hearing post request created', [], 'case'));

            return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId(), 'hearing' => $hearing->getId()]);
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
            $hearingPost->getDocument()->setUploadedAt(new DateTime('now'));

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

        $caseDocuments = $documentRepository->getAvailableDocumentsForCase($case);

        $form = $this->createForm(HearingPostRequestType::class, $hearingPost, [
            'case_parties' => $availableParties,
            'mail_template_choices' => $mailTemplates,
            'available_case_documents' => $caseDocuments,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // It is not sufficient to recreate document only if mail template is switched
            $customData = [];

            foreach ($form->get('customData') as $customField) {
                $name = $customField->getName();
                $customData[$name] = $customField->getData();
            }

            $hearingPost->setCustomData($customData);

            // Create new file
            $fileName = $mailTemplateHelper->renderMailTemplate($hearingPost->getTemplate(), $hearingPost);

            // For now, we just overwrite completely
            $documentUploader->replaceFileContent($hearingPost->getDocument(), $fileName);

            // Update Document
            /** @var User $user */
            $user = $this->getUser();
            $hearingPost->getDocument()->setDocumentName($hearingPost->getTitle());
            $hearingPost->getDocument()->setUploadedBy($user);
            $hearingPost->getDocument()->setUploadedAt(new DateTime('now'));

            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Hearing post request updated', [], 'case'));

            return $this->redirectToRoute('case_hearing_post_show', ['case' => $case->getId(), 'hearingPost' => $hearingPost->getId()]);
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
    public function hearingPostApprove(CaseEntity $case, HearingPostResponse $hearingPost, MailTemplateHelper $mailTemplateHelper, DocumentUploader $documentUploader, DigitalPostHelper $digitalPostHelper): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearingPost->getHearing()->getFinishedOn()) {
            throw new HearingException();
        }

        // Send receipt
        if ($hearingPost->getSendReceipt()) {
            $sender = $hearingPost->getSender();
            $digitalPostRecipients = [
                (new DigitalPost\Recipient())
                    ->setName($sender->getName())
                    ->setIdentifierType($sender->getIdentification()->getType())
                    ->setIdentifier($sender->getIdentification()->getIdentifier())
                    ->setAddress($sender->getAddress())
            ];
            $case = $hearingPost->getHearing()?->getCaseEntity();
            $template = $case?->getBoard()?->getReceiptHearingPost();

            if (null === $case) {
                $message = sprintf('Coild not get case.');
                throw new HearingException($message);
            }
            if (null === $template) {
                $message = sprintf('Could not get hearing post receipt template.');
                throw new HearingException($message);
            }

            $documentTitle = $this->translator->trans('Hearing post response receipt', [], 'case');
            // The document type is translated in templates/translations/mail_template.html.twig
            $documentType = 'Hearing post response created receipt';

            $fileName = $mailTemplateHelper->renderMailTemplate($template, $hearingPost);
            $user = $case->getAssignedTo();
            $document = $documentUploader->createDocumentFromPath($fileName, $documentTitle, $documentType, $user);

            // Create case document relation
            $relation = (new CaseDocumentRelation())
                ->setCase($case)
                ->setDocument($document)
            ;

            $this->entityManager->persist($relation);
            $this->entityManager->persist($document);

            $digitalPostHelper->createDigitalPost($document, $documentTitle, $case::class, $case->getId(), [], $digitalPostRecipients);
        }

        $today = new DateTime('today');
        $hearingPost->setApprovedOn($today);
        $hearingPost->getHearing()->setHasNewHearingPost(false);
        $this->entityManager->flush();
        $this->addFlash('success', new TranslatableMessage('Hearing post approved', [], 'case'));

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/forward", name="case_hearing_post_forward", methods={"POST"})
     */
    public function hearingPostForward(CaseEntity $case, HearingPostRequest $hearingPost, DigitalPostHelper $digitalPostHelper): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearingPost->getHearing()->getFinishedOn()) {
            throw new HearingException();
        }

        //Create DigitalPost attachments without linking them to a specific DigitalPost
        $digitalPostAttachments = [];

        $attachments = $hearingPost->getAttachments();

        foreach ($attachments as $attachment) {
            $digitalPostAttachment = new DigitalPostAttachment();
            $digitalPostAttachment->setDocument($attachment->getDocument());
            $digitalPostAttachments[] = $digitalPostAttachment;
        }

        // Handle recipients
        $digitalPostRecipients = [];

        $digitalPostRecipients[] = (new DigitalPost\Recipient())
            ->setName($hearingPost->getRecipient()->getName())
            ->setIdentifierType($hearingPost->getRecipient()->getIdentification()->getType())
            ->setIdentifier($hearingPost->getRecipient()->getIdentification()->getIdentifier())
            ->setAddress($hearingPost->getRecipient()->getAddress())
        ;

        $digitalPostHelper->createDigitalPost($hearingPost->getDocument(), $hearingPost->getTitle(), $case::class, $case->getId(), $digitalPostAttachments, $digitalPostRecipients);

        $today = new DateTime('today');

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
        $today = new DateTime('today');
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
    public function hearingPostDelete(CaseEntity $case, HearingPost $hearingPost, DocumentUploader $documentUploader, CaseDocumentRelationRepository $relationRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$hearingPost->getId(), $request->request->get('_token'))) {
            // Remove relation between case and document
            $document = $hearingPost->getDocument();
            $relation = $relationRepository->findOneBy(['case' => $case, 'document' => $document]);

            if (null !== $relation) {
                $this->entityManager->remove($relation);
                $this->entityManager->flush();
            }

            // Remove file
            $documentUploader->deleteDocumentFile($document);

            // Remove reference between post and document
            $hearingPost->setDocument(null);
            $this->entityManager->flush();

            // Remove new hearing post alert
            $hearingPost->getHearing()->setHasNewHearingPost(false);

            // Remove hearing post and document
            $this->entityManager->remove($hearingPost);
            $this->entityManager->remove($document);
            $this->entityManager->flush();

            $message = match ($hearingPost::class) {
                HearingPostResponse::class => new TranslatableMessage('Hearing post response deleted', [], 'case'),
                default => new TranslatableMessage('Hearing post request deleted', [], 'case'),
            };

            $this->addFlash('success', $message);
        }

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }
}
