<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaCaseItem;
use App\Entity\AgendaItem;
use App\Entity\AgendaManuelItem;
use App\Form\AgendaItemType;
use App\Service\AgendaItemHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

#[Route(path: '/agenda/{id}/item')]
class AgendaItemController extends AbstractController
{
    public function __construct(private readonly AgendaItemHelper $agendaItemHelper, private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/create', name: 'agenda_item_create', methods: ['GET', 'POST'])]
    public function create(Agenda $agenda, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        $form = $this->createForm(AgendaItemType::class, null, [
            'board' => $agenda->getBoard(),
        ]);

        $isFinishedAgenda = $agenda->isFinished();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$isFinishedAgenda) {
            $agendaItem = $form->get('agendaItem')->getData();

            if (AgendaCaseItem::class === $agendaItem::class) {
                $agendaItem->getCaseEntity()->setDateForActiveAgenda($agenda->getDate());

                $agendaItem->setInspection(
                    $agendaItem->getCaseEntity()->getShouldBeInspected()
                );
            }

            $agenda->addAgendaItem($agendaItem);
            $this->entityManager->persist($agendaItem);
            $this->entityManager->flush();
            $message = match ($agendaItem::class) {
                AgendaManuelItem::class => new TranslatableMessage('Agenda manual item created', [], 'agenda'),
                default => new TranslatableMessage('Agenda case item created', [], 'agenda'),
            };
            $this->addFlash('success', $message);

            return $this->redirectToRoute('agenda_show', ['id' => $agenda->getId()]);
        }

        return $this->render('agenda_item/new.html.twig', [
            'agenda_item_create_form' => $form->createView(),
            'agenda' => $agenda,
        ]);
    }

    /**
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @throws Exception
     */
    #[Route(path: '/{agenda_item_id}/edit', name: 'agenda_item_edit', methods: ['GET', 'POST'])]
    public function edit(Agenda $agenda, AgendaItem $agendaItem, Request $request): Response
    {
        $formClass = $this->agendaItemHelper->getFormType($agendaItem);
        $templatePath = $this->agendaItemHelper->getTemplatePath($agendaItem);

        $isFinishedAgenda = $agenda->isFinished();

        $options = ($isFinishedAgenda || $this->isGranted('ROLE_BOARD_MEMBER')) ? ['disabled' => true] : [];

        $form = $this->createForm($formClass, $agendaItem, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$isFinishedAgenda) {
            // We use the same route for showing and editing agenda items
            $this->denyAccessUnlessGranted('edit', $agendaItem);

            $this->entityManager->flush();
            $message = match ($agendaItem::class) {
                AgendaManuelItem::class => new TranslatableMessage('Agenda manual item updated', [], 'agenda'),
                default => new TranslatableMessage('Agenda case item updated', [], 'agenda'),
            };
            $this->addFlash('success', $message);

            return $this->redirectToRoute('agenda_item_edit', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
                'agenda_item' => $agendaItem,
            ]);
        }

        return $this->render($templatePath, [
            'agenda_item_edit_form' => $form->createView(),
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
        ]);
    }

    /**
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    #[Route(path: '/{agenda_item_id}', name: 'agenda_item_delete', methods: ['DELETE'])]
    public function delete(Agenda $agenda, AgendaItem $agendaItem, Request $request): Response
    {
        $this->denyAccessUnlessGranted('delete', $agendaItem);

        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$agendaItem->getId(), $request->request->get('_token')) && !$agenda->isFinished()) {
            if ($agendaItem instanceof AgendaCaseItem) {
                $agendaItem->getCaseEntity()->setDateForActiveAgenda(null);
            }
            $this->entityManager->remove($agendaItem);
            $this->entityManager->flush();
            $message = match ($agendaItem::class) {
                AgendaManuelItem::class => new TranslatableMessage('Agenda manual item deleted', [], 'agenda'),
                default => new TranslatableMessage('Agenda case item deleted', [], 'agenda'),
            };
            $this->addFlash('success', $message);
        }

        return $this->redirectToRoute('agenda_show', [
            'agenda' => $agenda,
            'id' => $agenda->getId(),
        ]);
    }
}
