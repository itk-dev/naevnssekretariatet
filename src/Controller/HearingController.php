<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\Hearing;
use App\Entity\HearingPost;
use App\Form\HearingFinishType;
use App\Form\HearingPostType;
use App\Repository\DocumentRepository;
use App\Repository\HearingPostRepository;
use App\Service\MailTemplateHelper;
use App\Service\PartyHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        // Check whether there is at least one part for each side
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

        // Detect whether most recent hearing post has been forwarded or even exists
        $hasNewUnforwardedPost = $hearingPosts[0] ? !$hearingPosts[0]->getHasBeenProcessedAndForwarded() : false;

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
     * @Route("/{case}/hearing/{hearing}/create", name="case_hearing_post_create")
     */
    public function hearingPostCreate(CaseEntity $case, DocumentRepository $documentRepository, Hearing $hearing, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, Request $request): Response
    {
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
            $hearing->setHasNewHearingPost(true);
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
     * @Route("/{case}/hearing/{hearingPost}", name="case_hearing_post_show")
     */
    public function hearingPostShow(CaseEntity $case, HearingPost $hearingPost, PartyHelper $partyHelper, Request $request): Response
    {
        return $this->render('case/hearing/post_show.html.twig', [
            'case' => $case,
            'hearingPost' => $hearingPost,
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearingPost}/edit", name="case_hearing_post_edit")
     */
    public function hearingPostEdit(CaseEntity $case, DocumentRepository $documentRepository, HearingPost $hearingPost, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, Request $request): Response
    {
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
        // TODO: Send digital post envelope containing hearing post

        $hearingPost->setHasBeenProcessedAndForwarded(true);
        $hearingPost->getHearing()->setHasNewHearingPost(false);
        $this->entityManager->flush();

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }
}
