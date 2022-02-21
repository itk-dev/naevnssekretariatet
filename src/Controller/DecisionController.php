<?php

namespace App\Controller;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\Decision;
use App\Entity\DigitalPost;
use App\Entity\DigitalPostAttachment;
use App\Entity\Document;
use App\Entity\User;
use App\Form\DecisionType;
use App\Repository\DecisionRepository;
use App\Repository\DocumentRepository;
use App\Service\DocumentUploader;
use App\Service\PartyHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function index(CaseEntity $case, DecisionRepository $decisionRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $decisions = $decisionRepository->findBy(['caseEntity' => $case->getId()], ['createdAt' => Criteria::DESC]);

        return $this->render('case/decision/index.html.twig', [
            'case' => $case,
            'decisions' => $decisions,
        ]);
    }

    /**
     * @Route("/create", name="case_decision_create")
     */
    public function create(CaseEntity $case, DocumentUploader $documentUploader, DocumentRepository $documentRepository, PartyHelper $partyHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        //TODO: Rename function
        $availableRecipients = $partyHelper->getRelevantPartiesForHearingPostByCase($case);

        $caseDocuments = $documentRepository->getAvailableDocumentsForCase($case);

        $documentUploader->specifyDirectory('/case_documents/');

        $decision = new Decision();

        $form = $this->createForm(DecisionType::class, $decision, [
            'available_recipients' => $availableRecipients,
            'available_case_documents' => $caseDocuments,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $document = new Document();
            $file = $form->get('filename')->getData();
            $newFilename = $documentUploader->upload($file);

            // Set filename, document name, creator and case
            $document->setFilename($newFilename);

            /** @var User $uploader */
            $uploader = $this->getUser();
            $document->setUploadedBy($uploader);

            $document->setDocumentName($decision->getTitle());
            $document->setType('Decision');

            $relation = new CaseDocumentRelation();
            $relation->setCase($case);
            $relation->setDocument($document);

            $decision->setDocument($document);
            $decision->setCaseEntity($case);

            // Create DigitalPost
            $digitalPost = new DigitalPost();
            $digitalPost->setDocument($decision->getDocument());
            $digitalPost->setEntityType(get_class($case));
            $digitalPost->setEntityId($case->getId());

            foreach ($decision->getRecipients() as $recipient) {
                $recipient = (new DigitalPost\Recipient())
                    ->setName($recipient->getName())
                    ->setIdentifierType($recipient->getIdentifierType())
                    ->setIdentifier($recipient->getIdentifier())
                    ->setAddress($recipient->getAddress())
                ;
                $digitalPost->addRecipient($recipient);
            }

            // Handle attachments
            $attachments = $decision->getAttachments();

            foreach ($attachments as $attachment) {
                $digitalPostAttachment = new DigitalPostAttachment();
                $digitalPostAttachment->setDocument($attachment->getDocument());

                $digitalPost->addAttachment($digitalPostAttachment);

                $this->entityManager->persist($digitalPostAttachment);
            }

            $this->entityManager->persist($digitalPost);
            $this->entityManager->persist($decision);
            $this->entityManager->persist($document);
            $this->entityManager->persist($relation);
            $this->entityManager->flush();

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
