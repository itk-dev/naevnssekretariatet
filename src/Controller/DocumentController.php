<?php

namespace App\Controller;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Exception\DocumentDirectoryException;
use App\Exception\FileMovingException;
use App\Form\CopyDocumentForm;
use App\Form\DocumentFilterType;
use App\Form\DocumentRelationDeleteType;
use App\Form\DocumentType;
use App\Repository\CaseDocumentRelationRepository;
use App\Repository\DocumentRepository;
use App\Service\CaseEventHelper;
use App\Service\DocumentCopyHelper;
use App\Service\DocumentUploader;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @Route("/case/{id}/documents")
 */
class DocumentController extends AbstractController
{
    private array $serviceOptions;

    public function __construct(private EntityManagerInterface $entityManager, private DocumentCopyHelper $copyHelper, private DocumentUploader $documentUploader, private CaseEventHelper $caseEventHelper, array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);
    }

    /**
     * @Route("/", name="document_index", methods={"GET"})
     */
    public function index(Request $request, CaseEntity $case, DocumentRepository $documentRepository, FilterBuilderUpdaterInterface $filterBuilderUpdater, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $filterOptions = [
            'case' => $case,
            'method' => 'get',
            'action' => $this->generateUrl('document_index', ['id' => $case->getId()]),
        ];
        $filterForm = $this->createForm(DocumentFilterType::class, null, $filterOptions);
        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->get($filterForm->getName()));
        }

        $filterBuilder = $documentRepository->createAvailableDocumentsForCaseQueryBuilder('d', $case);
        $filterBuilderUpdater->addFilterConditions($filterForm, $filterBuilder);
        $query = $filterBuilder->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), /*page number*/
            10,
            [
                'defaultSortFieldName' => 'd.uploadedAt',
                'defaultSortDirection' => Criteria::DESC,
            ]
        );

        $pagination->setCustomParameters(['align' => 'center']);

        return $this->render('documents/index.html.twig', [
            'filter_form' => $filterForm->createView(),
            'case' => $case,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/create", name="document_create", methods={"GET", "POST"})
     *
     * @throws FileMovingException
     * @throws DocumentDirectoryException
     */
    public function create(CaseEntity $case, CaseEventHelper $caseEventHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // Create new document and its form
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document, [
            'case' => $case,
            'view_timezone' => $this->serviceOptions['view_timezone'],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentType = $document->getType();
            /** @var UploadedFile[] $files */
            $files = $form->get('files')->getData();
            $numberOfDocuments = count($files);

            $documents = [];

            foreach ($files as $index => $file) {
                // Users will only see document name, not filename
                $documentName = 1 === $numberOfDocuments ? $document->getDocumentName() : sprintf('%s %d af %d', $document->getDocumentName(), $index + 1, $numberOfDocuments);

                $newDocument = $this->documentUploader->createDocumentFromUploadedFile($file, $documentName, $documentType);

                $documents[] = $newDocument;

                $relation = new CaseDocumentRelation();
                $relation->setCase($case);
                $relation->setDocument($newDocument);

                $this->entityManager->persist($newDocument);
                $this->entityManager->persist($relation);
            }

            if (DocumentType::CASE_EVENT_OPTION_YES === $form->get('createCaseEvent')->getData()) {
                $this->handleCaseEventCreation($case, $documents, $form->get('caseEvent'));
            }

            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('{count, plural, =1 {One document created} other {# documents created}}', ['count' => count($files)], 'documents'));

            return $this->redirectToRoute('document_index', ['id' => $case->getId()]);
        }

        return $this->render('documents/create.html.twig', [
            'case' => $case,
            'document_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{document}", name="document_edit", methods={"GET", "POST"})
     *
     * @throws FileMovingException
     * @throws DocumentDirectoryException
     */
    public function edit(CaseEntity $case, Document $document, CaseEventHelper $caseEventHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);
        $form = $this->createForm(DocumentType::class, $document, [
            'case' => $case,
            'view_timezone' => $this->serviceOptions['view_timezone'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (DocumentType::CASE_EVENT_OPTION_YES === $form->get('createCaseEvent')->getData()) {
                $this->handleCaseEventCreation($case, [$document], $form->get('caseEvent'));
            }

            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Document updated', [], 'documents'));

            return $this->redirectToRoute('document_index', ['id' => $case->getId()]);
        }

        return $this->render('documents/edit.html.twig', [
            'case' => $case,
            'document_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{document_id}", name="document_delete", methods={"GET", "DELETE"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("case", expr="repository.find(id)")
     */
    public function delete(Request $request, Document $document, CaseEntity $case, CaseDocumentRelationRepository $relationRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $relation = $relationRepository->findOneBy(['case' => $case, 'document' => $document]);

        $deleteForm = $this->createForm(DocumentRelationDeleteType::class, $relation, ['method' => 'DELETE']);

        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            // Simply just soft delete by setting soft deleted to true
            $relation->setSoftDeleted(true);
            $dateTime = new \DateTime('NOW');
            $relation->setSoftDeletedAt($dateTime);

            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Document deleted', [], 'documents'));

            $redirectUrl = $this->generateUrl('document_index', ['id' => $case->getId()]);

            return $this->redirect($redirectUrl);
        }

        return $this->render('documents/_delete.html.twig', [
            'delete_form' => $deleteForm->createView(),
            'case' => $case,
            'document' => $document,
        ]);
    }

    /**
     * @Route("/copy/{document_id}", name="document_copy", methods={"GET", "POST"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("case", expr="repository.find(id)")
     */
    public function copy(Request $request, Document $document, CaseEntity $case, CaseDocumentRelationRepository $relationRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // Find suitable cases
        $suitableCases = $this->copyHelper->findSuitableCases($case, $document);

        $form = $this->createForm(CopyDocumentForm::class, null, ['case' => $case, 'suitableCases' => $suitableCases]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cases = $form->get('cases')->getData();

            $this->copyHelper->handleCopyForm($cases, $document);

            return $this->redirectToRoute('document_index', ['id' => $case->getId()]);
        }

        return $this->render('documents/copy.html.twig', [
            'copy_document_form' => $form->createView(),
            'case' => $case,
        ]);
    }

    /**
     * @Route("/view/{document_id}", name="document_view", methods={"GET", "POST"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("case", expr="repository.find(id)")
     *
     * @throws DocumentDirectoryException
     */
    public function view(CaseEntity $case, Document $document, DocumentUploader $uploader): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $response = $uploader->handleViewDocument($document);

        return $response;
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('view_timezone')
        ;
    }

    private function handleCaseEventCreation(CaseEntity $case, array $documents, FormInterface $form)
    {
        $subject = $form->get('subject')->getData();
        $receivedAt = $form->get('receivedAt')->getData();
        $senders = $form->get('senders')->getData();
        $additionalSenders = $form->get('additionalSenders')->getData();
        $recipients = $form->get('recipients')->getData();
        $additionalRecipients = $form->get('additionalRecipients')->getData();

        $this->caseEventHelper->createDocumentCaseEvent($case, $subject, $senders, $additionalSenders, $recipients, $additionalRecipients, $documents, $receivedAt);

        $this->addFlash('success', new TranslatableMessage('Case event created', [], 'case_event'));
    }
}
