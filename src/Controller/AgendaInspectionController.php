<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaCaseItem;
use App\Entity\DigitalPost;
use App\Entity\Document;
use App\Entity\InspectionLetter;
use App\Form\InspectionLetterType;
use App\Repository\DigitalPostRepository;
use App\Service\CprHelper;
use App\Service\DigitalPostHelper;
use App\Service\DocumentUploader;
use App\Service\MailTemplateHelper;
use App\Service\PartyHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/agenda/{id}/item/{agenda_item_id}/inspection")
 */
class AgendaInspectionController extends AbstractController
{
    /**
     * @Route("/", name="agenda_case_item_inspection", methods={"GET"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function index(Agenda $agenda, AgendaCaseItem $agendaItem, DigitalPostRepository $digitalPostRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $agendaItem);

        return $this->render('agenda_case_item/inspection/index.html.twig', [
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
            'digital_posts' => $digitalPostRepository->findByEntity($agendaItem, [], ['createdAt' => Criteria::DESC]),
        ]);
    }

    /**
     * @Route("/create", name="agenda_case_item_inspection_letter_create", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function create(Agenda $agenda, AgendaCaseItem $agendaItem, CprHelper $cprHelper, DigitalPostHelper $digitalPostHelper, DocumentUploader $documentUploader, EntityManagerInterface $entityManager, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $agendaItem);

        $mailTemplates = $mailTemplateHelper->getTemplates('agenda_inspection');

        $availableRecipients = $partyHelper->getRelevantPartiesForHearingPostByCase($agendaItem->getCaseEntity());

        $inspection = new InspectionLetter();

        $form = $this->createForm(InspectionLetterType::class, $inspection, [
            'mail_template_choices' => $mailTemplates,
            'available_recipients' => $availableRecipients,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $customData = [];

            foreach ($form->get('customData') as $customField) {
                $name = $customField->getName();
                $customData[$name] = $customField->getData();
            }

            $inspection->setCustomData($customData);

            // Create new file from template
            $fileName = $mailTemplateHelper->renderMailTemplate($inspection->getTemplate(), $inspection);

            // Create document
            $document = $documentUploader->createDocumentFromPath($fileName, $inspection->getTitle(), 'Agenda inspection');

            $entityManager->persist($document);

            $inspection->setDocument($document);

            // Create recipients
            $digitalPostRecipients = [];

            foreach ($inspection->getRecipients() as $recipient) {
                $digitalPostRecipients[] = (new DigitalPost\Recipient())
                    ->setName($recipient->getName())
                    ->setIdentifierType($recipient->getIdentification()->getType())
                    ->setIdentifier($recipient->getIdentification()->getIdentifier())
                    ->setAddress($recipient->getAddress())
                ;
            }

            $inspection->setAgendaCaseItem($agendaItem);

            $entityManager->persist($inspection);

            $digitalPostHelper->createDigitalPost($document, $inspection->getTitle(), $agendaItem::class, $agendaItem->getId(), [], $digitalPostRecipients);

            return $this->redirectToRoute('agenda_case_item_inspection', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_case_item/inspection/create.html.twig', [
            'inspection_letter_form' => $form->createView(),
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
        ]);
    }

    /**
     * @Route("/{digital_post}/show", name="agenda_case_item_inspection_letter_show", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("digitalPost", expr="repository.find(digital_post)")
     */
    public function show(Agenda $agenda, AgendaCaseItem $agendaItem, DigitalPost $digitalPost)
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        return $this->render('agenda_case_item/inspection/show.html.twig', [
            'digital_post' => $digitalPost,
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
        ]);
    }

    /**
     * @Route("/view/{document}", name="agenda_case_item_inspection_document_view", methods={"GET"})
     */
    public function view(Agenda $agenda, Document $document, DocumentUploader $uploader): Response
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        $response = $uploader->handleViewDocument($document);

        return $response;
    }
}
