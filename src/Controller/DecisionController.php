<?php

namespace App\Controller;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\Decision;
use App\Entity\DigitalPost;
use App\Entity\DigitalPostAttachment;
use App\Form\DecisionType;
use App\Repository\CasePartyRelationRepository;
use App\Repository\DecisionRepository;
use App\Repository\DocumentRepository;
use App\Service\DigitalPostHelper;
use App\Service\DocumentUploader;
use App\Service\PartyHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @Route("/case/{id}/decision")
 */
class DecisionController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @Route("/", name="case_decision", methods={"GET"})
     */
    public function index(CaseEntity $case, CasePartyRelationRepository $casePartyRelationRepository, DecisionRepository $decisionRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $decisions = $decisionRepository->findBy(['caseEntity' => $case->getId()], ['createdAt' => Criteria::DESC]);

        $hasNoActiveParty = count($casePartyRelationRepository->findBy(['case' => $case, 'softDeleted' => false])) < 1;

        return $this->render('case/decision/index.html.twig', [
            'case' => $case,
            'decisions' => $decisions,
            'hasNoActiveParty' => $hasNoActiveParty,
        ]);
    }

    /**
     * @Route("/create", name="case_decision_create")
     */
    public function create(CaseEntity $case, DigitalPostHelper $digitalPostHelper, DocumentUploader $documentUploader, DocumentRepository $documentRepository, PartyHelper $partyHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        //TODO: Rename function
        $availableRecipients = $partyHelper->getRelevantPartiesForHearingPostByCase($case);

        $caseDocuments = $documentRepository->getAvailableDocumentsForCase($case);

        $decision = new Decision();

        $form = $this->createForm(DecisionType::class, $decision, [
            'available_recipients' => $availableRecipients,
            'available_case_documents' => $caseDocuments,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('filename')->getData();

            $document = $documentUploader->createDocumentFromUploadedFile($file, $decision->getTitle(), 'Decision');

            $relation = new CaseDocumentRelation();
            $relation->setCase($case);
            $relation->setDocument($document);

            $decision->setDocument($document);
            $decision->setCaseEntity($case);

            $this->entityManager->persist($decision);
            $this->entityManager->persist($document);
            $this->entityManager->persist($relation);

            //Create DigitalPost attachments without linking them to a specific DigitalPost
            $digitalPostAttachments = [];

            $attachments = $decision->getAttachments();

            foreach ($attachments as $attachment) {
                $digitalPostAttachment = new DigitalPostAttachment();
                $digitalPostAttachment->setDocument($attachment->getDocument());
                $digitalPostAttachments[] = $digitalPostAttachment;
            }

            // Handle recipients
            $digitalPostRecipients = [];

            foreach ($decision->getRecipients() as $recipient) {
                $digitalPostRecipients[] = (new DigitalPost\Recipient())
                    ->setName($recipient->getName())
                    ->setIdentifierType($recipient->getIdentification()->getType())
                    ->setIdentifier($recipient->getIdentification()->getIdentifier())
                    ->setAddress($recipient->getAddress())
                ;
            }

            $digitalPostHelper->createDigitalPost($document, $decision->getTitle(), get_class($case), $case->getId(), $digitalPostAttachments, $digitalPostRecipients);

            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Decision created', [], 'decision'));

            return $this->redirectToRoute('case_decision', ['id' => $case->getId()]);
        }

        return $this->render('case/decision/create.html.twig', [
            'case' => $case,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{decision_id}/show", name="case_decision_show")
     * @Entity("decision", expr="repository.find(decision_id)")
     * @Entity("case", expr="repository.find(id)")
     */
    public function show(CaseEntity $case, Decision $decision): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        return $this->render('case/decision/show.html.twig', [
            'case' => $case,
            'decision' => $decision,
        ]);
    }
}
