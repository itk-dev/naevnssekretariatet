<?php

namespace App\Controller;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\DigitalPost;
use App\Entity\DigitalPostAttachment;
use App\Entity\Document;
use App\Entity\Hearing;
use App\Entity\HearingPost;
use App\Entity\User;
use App\Exception\HearingException;
use App\Form\HearingFinishType;
use App\Form\HearingPostType;
use App\Repository\DocumentRepository;
use App\Repository\HearingPostRepository;
use App\Service\DocumentUploader;
use App\Service\MailTemplateHelper;
use App\Service\PartyHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/case")
 */
class HearingController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @Route("/{id}/hearing", name="case_hearing_index")
     */
    public function hearing(CaseEntity $case, HearingPostRepository $hearingPostRepository, PartyHelper $partyHelper, Request $request): Response
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
        $hasNewUnforwardedPost = $mostRecentPost && !$mostRecentPost->getForwardedOn();

        return $this->render('case/hearing/index.html.twig', [
            'case' => $case,
            'hearing' => $hearing,
            'posts' => $hearingPosts,
            'neitherPartyHasAnythingToAdd' => $neitherPartyHasAnythingToAdd,
            'hasSufficientParties' => $hasSufficientParties,
            'hasNewUnforwardedPost' => $hasNewUnforwardedPost,
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
     * @Route("/{case}/hearing/{hearing}/create", name="case_hearing_post_create")
     */
    public function hearingPostCreate(CaseEntity $case, DocumentUploader $documentUploader, DocumentRepository $documentRepository, Hearing $hearing, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, Request $request, SluggerInterface $slugger, Filesystem $filesystem): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearing->getFinishedOn()) {
            throw new HearingException();
        }

        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);
        $mailTemplates = $mailTemplateHelper->getTemplates('hearing');

        $hearingPost = new HearingPost();

        $caseDocuments = $documentRepository->getAvailableDocumentsForCase($case);

        $form = $this->createForm(HearingPostType::class, $hearingPost, [
            'case_parties' => $availableParties,
            'mail_template_choices' => $mailTemplates,
            'available_case_documents' => $caseDocuments,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hearingPost->setHearing($hearing);

            // Create new file from template
            $fileName = $mailTemplateHelper->renderMailTemplate($hearingPost->getTemplate(), $case);

            // Compute fitting name
            $documentUploader->specifyDirectory('/case_documents/');
            $updatedFileName = $slugger->slug($hearingPost->getTemplate()->getName()).'-'.uniqid().'.pdf';

            // Rename file
            $filesystem->rename($fileName, $documentUploader->getDirectory().'/'.$updatedFileName, true);

            // Create document
            $document = new Document();
            $document->setFilename($updatedFileName);
            $document->setDocumentName($hearingPost->getDocumentName());
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
     * @Route("/{case}/hearing/{hearingPost}/edit", name="case_hearing_post_edit")
     */
    public function hearingPostEdit(CaseEntity $case, DocumentRepository $documentRepository, DocumentUploader $documentUploader, Filesystem $filesystem, HearingPost $hearingPost, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearingPost->getHearing()->getFinishedOn()) {
            throw new HearingException();
        }

        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);
        $mailTemplates = $mailTemplateHelper->getTemplates('hearing');

        $caseDocuments = $documentRepository->getAvailableDocumentsForCase($case);

        $form = $this->createForm(HearingPostType::class, $hearingPost, [
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
            $filesystem->rename($fileName, $documentUploader->getDirectory().'/'.$currentDocumentFileName, true);

            // Update Document
            /** @var User $user */
            $user = $this->getUser();
            $hearingPost->getDocument()->setDocumentName($hearingPost->getDocumentName());
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
     * @Route("/{case}/hearing/{hearingPost}/forward", name="case_hearing_post_forward")
     */
    public function hearingPostForward(CaseEntity $case, HearingPost $hearingPost): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearingPost->getHearing()->getFinishedOn()) {
            throw new HearingException();
        }

        // Create DigitalPost
        $digitalPost = new DigitalPost();
        $digitalPost->setDocument($hearingPost->getDocument());
        $digitalPost->setEntityType(get_class($case));
        $digitalPost->setEntityId($case->getId());

        // Handle attachments
        $attachments = $hearingPost->getAttachments();

        foreach ($attachments as $attachment) {
            $digitalPostAttachment = new DigitalPostAttachment();
            $digitalPostAttachment->setPosition($attachment->getPosition());
            $digitalPostAttachment->setDocument($attachment->getDocument());
            $digitalPostAttachment->setDigitalPost($digitalPost);

            $digitalPost->addAttachment($digitalPostAttachment);

            $this->entityManager->persist($digitalPostAttachment);
        }

        $this->entityManager->persist($digitalPost);

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
