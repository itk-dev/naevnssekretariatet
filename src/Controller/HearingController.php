<?php

namespace App\Controller;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Entity\Hearing;
use App\Entity\HearingPost;
use App\Entity\User;
use App\Form\HearingDocumentType;
use App\Form\HearingFinishType;
use App\Form\HearingPostType;
use App\Repository\CaseDocumentRelationRepository;
use App\Repository\HearingPostRepository;
use App\Service\DocumentUploader;
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
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @Route("/{id}/hearing", name="case_hearing")
     */
    public function hearing(CaseEntity $case, HearingPostRepository $hearingPostRepository, PartyHelper $partyHelper, Request $request): Response
    {
        $relevantParties = $partyHelper->getRelevantPartiesByCase($case);

        $hasSufficientParties = sizeof($relevantParties['complainants']) > 0 && sizeof($relevantParties['counterparties']) > 0;

        $hearing = $case->getHearing();

        if (null === $hearing) {
            $hearing = new Hearing();
            $case->setHearing($hearing);

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

        return $this->render('case/hearing/index.html.twig', [
            'case' => $case,
            'hearing' => $hearing,
            'posts' => $hearingPosts,
            'neitherPartyHasAnythingToAdd' => $neitherPartyHasAnythingToAdd,
            'form' => $form->createView(),
            'hasSufficientParties' => $hasSufficientParties,
        ]);
    }

    /**
     * @Route("/{id}/hearing/start", name="case_hearing_start")
     */
    public function startHearing(CaseEntity $case): Response
    {
        $hearing = $case->getHearing();

        // Mark hearing as started
        $hearing->setHasBeenStarted(true);
        $today = new DateTime('today');
        $hearing->setStartDate($today);
        $this->entityManager->persist($hearing);
        $this->entityManager->flush();

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }

    /**
     * @Route("/{id}/hearing/index", name="case_hearing_index")
     */
    public function activeHearing(CaseEntity $case, HearingPostRepository $hearingPostRepository, Request $request): Response
    {
        $hearing = $case->getHearing();

        $neitherPartyHasAnythingToAdd = true === $hearing->getComplainantHasNoMoreToAdd() && true === $hearing->getCounterpartHasNoMoreToAdd();

        $form = $this->createForm(HearingFinishType::class, $hearing);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
        }

        $hearingPosts = $hearingPostRepository->findBy(['hearing' => $hearing], ['createdAt' => 'DESC']);

        return $this->render('case/hearing/index.html.twig', [
            'case' => $case,
            'hearing' => $hearing,
            'posts' => $hearingPosts,
            'neitherPartyHasAnythingToAdd' => $neitherPartyHasAnythingToAdd,
            'form' => $form->createView(),
        ]);
    }

//    /**
//     * @Route("/{id}/start-hearing", name="case_hearing_start")
//     */
//    public function startHearing(CaseEntity $case, PartyHelper $partyHelper): Response
//    {
//        $relevantParties = $partyHelper->getRelevantPartiesByCase($case);
//
//        $hasSufficientParties = sizeof($relevantParties['complainants']) > 0 && sizeof($relevantParties['counterparties']) > 0;
//
//        if ($hasSufficientParties) {
//            $hearing = new Hearing();
//            $case->setHearing($hearing);
//
//            $this->entityManager->persist($hearing);
//            $this->entityManager->flush();
//        }
//
//        return $this->render('case/hearing/_start_hearing.html.twig', [
//            'case' => $case,
//            'hasSufficientParties' => $hasSufficientParties,
//        ]);
//    }

    /**
     * @Route("/{case}/hearing/{hearing}/create", name="case_hearing_post_create")
     */
    public function hearingPostCreate(CaseEntity $case, Hearing $hearing, PartyHelper $partyHelper, Request $request): Response
    {
        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);

        $hearingPost = new HearingPost();

        $form = $this->createForm(HearingPostType::class, $hearingPost, [
            'case_parties' => $availableParties,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hearingPost->setHearing($hearing);
            $hearing->setHasNewHearingPost(true);
            $this->entityManager->persist($hearingPost);
            $this->entityManager->flush();

            return $this->redirectToRoute('case_hearing', ['id' => $case->getId(), 'hearing' => $hearing->getId()]);
        }

        return $this->render('case/hearing/post_create.html.twig', [
            'case' => $case,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}", name="case_hearing_post_show")
     */
    public function hearingPostShow(CaseEntity $case, HearingPost $hearingPost, PartyHelper $partyHelper, Request $request): Response
    {
        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);

        // TODO: Show in some other way, not via form.
        $form = $this->createForm(HearingPostType::class, $hearingPost, [
            'case_parties' => $availableParties,
            'disabled' => true,
        ]);

        return $this->render('case/hearing/post_show.html.twig', [
            'case' => $case,
            'hearingPost' => $hearingPost,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/edit", name="case_hearing_post_edit")
     */
    public function hearingPostEdit(CaseEntity $case, HearingPost $hearingPost, PartyHelper $partyHelper, Request $request): Response
    {
        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);

        $form = $this->createForm(HearingPostType::class, $hearingPost, [
            'case_parties' => $availableParties,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
     * @Route("/{case}/hearing/{hearingPost}/upload-document", name="case_hearing_post_document_upload")
     */
    public function hearingPostDocumentUpload(CaseEntity $case, HearingPost $hearingPost, DocumentUploader $documentUploader, TranslatorInterface $translator, Request $request): Response
    {
        $documentUploader->specifyDirectory('/case_documents/');

        $document = new Document();
        $form = $this->createForm(HearingDocumentType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Extract filename and handle it
            // Users will only see document name, not filename
            $file = $form->get('filename')->getData();

            $newFilename = $documentUploader->upload($file);

            // Set filename, document name, creator and case
            $document->setFilename($newFilename);
            $document->setType($translator->trans('Hearing post', [], 'da'));

            /** @var User $uploader */
            $uploader = $this->getUser();
            $document->setUploadedBy($uploader);

            $relation = new CaseDocumentRelation();
            $relation->setCase($case);
            $relation->setDocument($document);
            $hearingPost->addDocument($document);

            $this->entityManager->persist($document);
            $this->entityManager->persist($relation);
            $this->entityManager->flush();

            return $this->redirectToRoute('case_hearing_post_show', ['case' => $case->getId(), 'hearingPost' => $hearingPost->getId()]);
        }

        return $this->render('case/hearing/document_upload.html.twig', [
            'case' => $case,
            'hearingPost' => $hearingPost,
            'document_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/document/{document}/delete", name="case_hearing_post_document_delete", methods={"DELETE"})
     */
    public function delete(Request $request, HearingPost $hearingPost, Document $document, CaseEntity $case, CaseDocumentRelationRepository $relationRepository): Response
    {
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            // Simply just soft delete by setting soft deleted to true

            $relation = $relationRepository->findOneBy(['case' => $case, 'document' => $document]);
            $relation->setSoftDeleted(true);
            $dateTime = new \DateTime('NOW');
            $relation->setSoftDeletedAt($dateTime);
            $hearingPost->removeDocument($document);

            $this->entityManager->flush();
        }

        return $this->redirectToRoute('case_hearing_post_show', ['case' => $case->getId(), 'hearingPost' => $hearingPost->getId()]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/forward", name="case_hearing_post_forward")
     */
    public function hearingPostForward(CaseEntity $case, HearingPost $hearingPost): Response
    {
        // TODO: Send digital post containing hearing post

        $hearingPost->setHasBeenProcessedAndForwarded(true);
        $hearingPost->getHearing()->setHasNewHearingPost(false);
        $this->entityManager->flush();

        return $this->redirectToRoute('case_hearing', ['id' => $case->getId()]);
    }
}
