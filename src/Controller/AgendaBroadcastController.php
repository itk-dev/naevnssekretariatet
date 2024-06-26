<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaBroadcast;
use App\Entity\DigitalPost;
use App\Entity\Document;
use App\Exception\CprException;
use App\Exception\DocumentDirectoryException;
use App\Form\AgendaBroadcastType;
use App\Repository\DigitalPostRepository;
use App\Service\CprHelper;
use App\Service\DigitalPostHelper;
use App\Service\DocumentUploader;
use App\Service\MailTemplateHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Itkdev\BeskedfordelerBundle\Helper\MessageHelper;
use ItkDev\Serviceplatformen\Service\Exception\NoPnrFoundException;
use ItkDev\Serviceplatformen\Service\Exception\ServiceException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/agenda/{id}/broadcast")
 */
class AgendaBroadcastController extends AbstractController
{
    /**
     * @Route("/index", name="agenda_broadcast", methods={"GET"})
     */
    public function index(Agenda $agenda, DigitalPostRepository $digitalPostRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        return $this->render('agenda/broadcast/index.html.twig', [
            'agenda' => $agenda,
            'digital_posts' => $digitalPostRepository->findByEntity($agenda, [], ['createdAt' => Criteria::DESC]),
        ]);
    }

    /**
     * @Route("/create", name="agenda_broadcast_create", methods={"GET", "POST"})
     */
    public function broadcastAgenda(Agenda $agenda, CprHelper $cprHelper, DigitalPostHelper $digitalPostHelper, DocumentUploader $documentUploader, EntityManagerInterface $entityManager, MailTemplateHelper $mailTemplateHelper, TranslatorInterface $translator, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        $mailTemplates = $mailTemplateHelper->getTemplates('agenda_broadcast');

        $agendaBroadcast = new AgendaBroadcast();

        $form = $this->createForm(AgendaBroadcastType::class, $agendaBroadcast, [
            'mail_template_choices' => $mailTemplates,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $customData = [];

            foreach ($form->get('customData') as $customField) {
                $name = $customField->getName();
                $customData[$name] = $customField->getData();
            }

            $agendaBroadcast->setCustomData($customData);

            // Create a recipient per board member
            $boardMembers = $agenda->getBoardmembers();
            $digitalPostRecipients = [];

            foreach ($boardMembers as $boardMember) {
                try {
                    $boardMemberAddress = $cprHelper->getAddressFromCpr($boardMember->getCpr());

                    $digitalPostRecipients[] = (new DigitalPost\Recipient())
                        ->setName($boardMember->getName())
                        ->setIdentifierType('CPR')
                        ->setIdentifier($boardMember->getCpr())
                        ->setAddress($boardMemberAddress)
                    ;
                } catch (CprException $e) {
                    $message = match (true) {
                        $e->getPrevious() instanceof NoPnrFoundException => $translator->trans('PNR for {boardMember} not found ({cprNumber})', ['boardMember' => $boardMember->getName(), 'cprNumber' => $boardMember->getCpr()], 'digital_post'),
                        $e->getPrevious() instanceof ServiceException => $e->getPrevious()->getMessage(),
                        default => $e->getMessage(),
                    };
                    $this->addFlash('danger', $message);

                    return $this->redirectToRoute('agenda_broadcast', [
                        'id' => $agenda->getId(),
                    ]);
                }
            }

            // Agenda needs to be set before rendering mail template.
            $agendaBroadcast->setAgenda($agenda);

            // Create new file from template
            $fileName = $mailTemplateHelper->renderMailTemplate($agendaBroadcast->getTemplate(), $agendaBroadcast);

            // Create document
            $document = $documentUploader->createDocumentFromPath($fileName, $agendaBroadcast->getTitle(), 'Agenda broadcast');

            $entityManager->persist($document);

            $agendaBroadcast->setDocument($document);

            $entityManager->persist($agendaBroadcast);

            $digitalPostHelper->createDigitalPost($document, $agendaBroadcast->getTitle(), get_class($agenda), $agenda->getId(), [], $digitalPostRecipients);

            return $this->redirectToRoute('agenda_broadcast', [
                'id' => $agenda->getId(),
            ]);
        }

        return $this->render('agenda/broadcast/create.html.twig', [
            'broadcast_form' => $form->createView(),
            'agenda' => $agenda,
        ]);
    }

    /**
     * @Route("/{digital_post}/show", name="agenda_broadcast_show", methods={"GET", "POST"})
     *
     * @Entity("digitalPost", expr="repository.find(digital_post)")
     */
    public function show(Agenda $agenda, DigitalPost $digitalPost, MessageHelper $messageHelper)
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        return $this->render('agenda/broadcast/show.html.twig', [
            'digital_post' => $digitalPost,
            'agenda' => $agenda,
            'message_helper' => $messageHelper,
        ]);
    }

    /**
     * @Route("/view/{document}", name="agenda_broadcast_document_view", methods={"GET"})
     *
     * @throws DocumentDirectoryException
     */
    public function view(Agenda $agenda, Document $document, DocumentUploader $uploader): Response
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        $response = $uploader->handleViewDocument($document);

        return $response;
    }
}
