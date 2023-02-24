<?php

namespace App\Controller;

use _PHPStan_3e014c27f\Nette\Utils\DateTime;
use App\Entity\CaseEntity;
use App\Entity\CaseEvent;
use App\Entity\CaseEventPartyRelation;
use App\Entity\Party;
use App\Form\CaseEventEditType;
use App\Form\CaseEventFilterType;
use App\Form\CaseEventNewType;
use App\Repository\CaseEventRepository;
use App\Service\PartyHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatableMessage;

#[Route('/case/{id}/case-events')]
class CaseEventController extends AbstractController
{
    #[Route('/', name: 'case_event_index', methods: ['GET'])]
    public function index(CaseEntity $case, CaseEventRepository $caseEventRepository, FilterBuilderUpdaterInterface $filterBuilderUpdater, PaginatorInterface $paginator, Request $request): Response
    {
        $filterOptions = [
            'case' => $case,
            'method' => 'get',
            'action' => $this->generateUrl('case_event_index', ['id' => $case->getId()]),
        ];

        $filterForm = $this->createForm(CaseEventFilterType::class, null, $filterOptions);
        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->get($filterForm->getName()));
        }

        $filterBuilder = $caseEventRepository->createAvailableCaseEventsForCaseQueryBuilder('ce', $case);
        $filterBuilderUpdater->addFilterConditions($filterForm, $filterBuilder);
        $query = $filterBuilder->getQuery();

        $pagination = $paginator->paginate(
            $query,
            1,
            1000, // Hopefully this is never reached.
            [
                'defaultSortFieldName' => 'ce.createdAt',
                'defaultSortDirection' => Criteria::DESC,
            ]
        );

        return $this->render('case/event/index.html.twig', [
            'filter_form' => $filterForm->createView(),
            'case' => $case,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{caseEvent}/show', name: 'case_event_show', methods: ['GET'])]
    public function show(CaseEntity $case, CaseEvent $caseEvent): Response
    {
        return $this->render('case/event/show.html.twig', [
            'case' => $case,
            'case_event' => $caseEvent,
        ]);
    }

    #[Route('/create', name: 'case_event_create', methods: ['GET', 'POST'])]
    public function create(CaseEntity $case, EntityManagerInterface $manager, PartyHelper $partyHelper, Request $request): Response
    {
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

        $parties = $partyHelper->getRelevantPartiesByCase($case);

        // Convert into choices for CaseEventNewType.
        $choices = [];
        foreach ($parties['parties'] as $data) {
            /** @var Party $party */
            $party = $data['party'];
            $type = $data['type'];
            $choices[$party->getName().', '.$type] = $party;
        }

        foreach ($parties['counterparties'] as $data) {
            /** @var Party $party */
            $party = $data['party'];
            $type = $data['type'];
            $choices[$party->getName().', '.$type] = $party;
        }

        // Setup form and handle it.
        $form = $this->createForm(CaseEventNewType::class, null, ['choices' => $choices]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $caseEvent = new CaseEvent();

            if (is_array($data['senders'])) {
                $this->createCaseEventRelations($caseEvent, $manager, CaseEventPartyRelation::TYPE_SENDER, $data['senders']);
            }

            if (is_array($data['recipients'])) {
                $this->createCaseEventRelations($caseEvent, $manager, CaseEventPartyRelation::TYPE_RECIPIENT, $data['recipients']);
            }

            $caseEvent->setSubject($data['subject']);
            $caseEvent->setNoteContent($data['noteContent']);
            $caseEvent->setCategory(CaseEvent::CATEGORY_NOTE);
            $caseEvent->setCaseEntity($case);
            $caseEvent->setCreatedBy($this->getUser());
            $caseEvent->setReceivedAt(new DateTime('now'));

            $manager->persist($caseEvent);
            $manager->flush();

            $this->addFlash('success', new TranslatableMessage('Case event created', [], 'case_event'));

            return $this->redirectToRoute('case_event_index', [
                'id' => $case->getId()->__toString(),
            ]);
        }

        return $this->render('case/event/create.html.twig', [
            'case' => $case,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{caseEvent}/edit', name: 'case_event_edit', methods: ['GET', 'POST'])]
    public function edit(CaseEntity $case, CaseEvent $caseEvent, EntityManagerInterface $manager, Request $request): Response
    {
        // Setup form and handle it.
        $form = $this->createForm(CaseEventEditType::class, $caseEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', new TranslatableMessage('Case event updated', [], 'case_event'));

            return $this->redirectToRoute('case_event_show', [
                'id' => $case->getId()->__toString(),
                'caseEvent' => $caseEvent->getId()->__toString(),
            ]);
        }

        return $this->render('case/event/edit.html.twig', [
            'case' => $case,
            'form' => $form->createView(),
        ]);
    }

    private function createCaseEventRelations(CaseEvent $caseEvent, EntityManagerInterface $manager, string $type, array $parties)
    {
        foreach ($parties as $party) {
            $caseEventPartyRelation = new CaseEventPartyRelation();
            $caseEventPartyRelation->setParty($party);
            $caseEventPartyRelation->setCaseEvent($caseEvent);
            $caseEventPartyRelation->setType($type);

            $manager->persist($caseEventPartyRelation);
        }
    }
}
