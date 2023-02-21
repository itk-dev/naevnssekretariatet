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
use App\Service\DocumentCopyHelper;
use App\Service\DocumentUploader;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @Route("/case/{id}/documents")
 */
class DocumentController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly DocumentCopyHelper $copyHelper, private readonly DocumentUploader $documentUploader)
    {
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
    public function create(CaseEntity $case, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // Create new document and its form
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Extract filename and handle it
            // Users will only see document name, not filename
            $documentName = $document->getDocumentName();
            $documentType = $document->getType();
            /** @var UploadedFile[] $files */
            $files = $form->get('files')->getData();
            foreach ($files as $file) {
                $newDocument = $this->documentUploader->createDocumentFromUploadedFile($file, $documentName, $documentType);

                $relation = new CaseDocumentRelation();
                $relation->setCase($case);
                $relation->setDocument($newDocument);

                $this->entityManager->persist($newDocument);
                $this->entityManager->persist($relation);
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
    public function edit(CaseEntity $case, Document $document, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);
        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
}
