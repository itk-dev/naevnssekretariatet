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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/case")
 */
class HearingController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private TranslatorInterface $translator)
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

        $hasSufficientParties = count($relevantParties['complainants']) > 0 && count($relevantParties['counterparties']) > 0;

        $hearing = $case->getHearing();

        if (null === $hearing) {
            $hearing = new Hearing();
            $case->setHearing($hearing);
            $hearing->setCaseEntity($case);

            $this->entityManager->persist($hearing);
            $this->entityManager->flush();
        }

        $neitherPartyHasAnythingToAdd = true === $hearing->getComplainantHasNoMoreToAdd() && true === $hearing->getCounterpartHasNoMoreToAdd();

        $form = $this->createForm(HearingFinishType::class, $hearing);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

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
            'neitherPartyHasAnythingToAdd' => $neitherPartyHasAnythingToAdd,
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

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }

    /**
     * @Route("/{case}/hearing/{hearing}/response/create", name="case_hearing_post_response_create")
     */
    public function hearingPostResponseCreate(CaseEntity $case, DocumentRepository $documentRepository, Hearing $hearing, PartyHelper $partyHelper, Request $request): Response
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

            $this->entityManager->persist($hearingPost);
            $this->entityManager->flush();

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
            $hearingPost->setHearing($hearing);

            // Create new file from template
            $fileName = $mailTemplateHelper->renderMailTemplate($hearingPost->getTemplate(), $case);

            // Move file
            $documentUploader->specifyDirectory('/case_documents/');
            $updatedFileName = $documentUploader->uploadFile($fileName);

            // Create document
            $document = new Document();
            $document->setFilename($updatedFileName);
            $document->setDocumentName($hearingPost->getTitle());
            $document->setHearingPost($hearingPost);
            $hearingPost->setDocument($document);

            /** @var User $user */
            $user = $this->getUser();
            $document->setUploadedBy($user);
            $document->setType('Hearing');

            // Create case document relation
            $relation = new CaseDocumentRelation();
            $relation->setCase($case);
            $relation->setDocument($document);

            $hearing->setHasNewHearingPost(true);
            $this->entityManager->persist($relation);
            $this->entityManager->persist($document);
            $this->entityManager->persist($hearingPost);
            $this->entityManager->flush();

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
    public function hearingPostResponseEdit(CaseEntity $case, DocumentRepository $documentRepository, HearingPost $hearingPost, PartyHelper $partyHelper, Request $request): Response
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
            $this->entityManager->persist($hearingPost);
            $this->entityManager->flush();

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
    public function hearingPostRequestEdit(CaseEntity $case, DocumentRepository $documentRepository, DocumentUploader $documentUploader, HearingPost $hearingPost, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, Request $request): Response
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

            // Create new file
            $fileName = $mailTemplateHelper->renderMailTemplate($hearingPost->getTemplate(), $case);

            // For now we just overwrite completely
            $currentDocumentFileName = $hearingPost->getDocument()->getFilename();
            $documentUploader->specifyDirectory('/case_documents/');
            $documentUploader->replaceFile($fileName, $currentDocumentFileName);

            // Update Document
            /** @var User $user */
            $user = $this->getUser();
            $hearingPost->getDocument()->setDocumentName($hearingPost->getTitle());
            $hearingPost->getDocument()->setUploadedBy($user);
            $hearingPost->getDocument()->setUploadedAt(new DateTime('now'));

            $this->entityManager->flush();

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
    public function hearingPostApprove(CaseEntity $case, HearingPostResponse $hearingPost): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearingPost->getHearing()->getFinishedOn()) {
            throw new HearingException();
        }
        $today = new DateTime('today');
        $hearingPost->setApprovedOn($today);
        $hearingPost->getHearing()->setHasNewHearingPost(false);
        $this->entityManager->flush();

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
            ->setIdentifierType($hearingPost->getRecipient()->getIdentifierType())
            ->setIdentifier($hearingPost->getRecipient()->getIdentifier())
            ->setAddress($hearingPost->getRecipient()->getAddress())
        ;

        $digitalPostHelper->createDigitalPost($hearingPost->getDocument(), $hearingPost->getTitle(), get_class($case), $case->getId(), $digitalPostAttachments, $digitalPostRecipients);

        $today = new DateTime('today');
        $hearingPost->setForwardedOn($today);
        $hearingPost->getHearing()->setHasNewHearingPost(false);
        $this->entityManager->flush();

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

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }
}
