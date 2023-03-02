<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\CaseEvent;
use App\Form\CaseEventEditType;
use App\Form\CaseEventFilterType;
use App\Form\CaseEventNewType;
use App\Repository\CaseEventRepository;
use App\Service\CaseEventHelper;
use App\Service\PartyHelper;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatableMessage;

#[Route('/case/{id}/case-events')]
class CaseEventController extends AbstractController
{
    private array $serviceOptions;

    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);
    }

    #[Route('/', name: 'case_event_index', methods: ['GET'])]
    public function index(CaseEntity $case, CaseEventRepository $caseEventRepository, FilterBuilderUpdaterInterface $filterBuilderUpdater, Request $request): Response
    {
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

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

        $caseEvents = $filterBuilder->getQuery()->getResult();

        return $this->render('case/event/index.html.twig', [
            'filter_form' => $filterForm->createView(),
            'case' => $case,
            'case_events' => $caseEvents,
        ]);
    }

    #[Route('/{caseEvent}/show', name: 'case_event_show', methods: ['GET'])]
    public function show(CaseEntity $case, CaseEvent $caseEvent): Response
    {
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

        return $this->render('case/event/show.html.twig', [
            'case' => $case,
            'case_event' => $caseEvent,
        ]);
    }

    #[Route('/create', name: 'case_event_create', methods: ['GET', 'POST'])]
    public function create(CaseEntity $case, PartyHelper $partyHelper, CaseEventHelper $caseEventHelper, Request $request): Response
    {
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

        $transformedPartyChoices = $partyHelper->getTransformedRelevantPartiesByCase($case);

        // Setup form and handle it.
        $form = $this->createForm(CaseEventNewType::class, null, [
            'choices' => $transformedPartyChoices,
            'view_timezone' => $this->serviceOptions['view_timezone'],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $caseEventHelper->createManualCaseEvent($case, $data['subject'], $data['noteContent'], $data['senders'], $data['additionalSenders'], $data['recipients'], $data['additionalRecipients'], $data['receivedAt']);

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
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

        // Setup form and handle it.
        $form = $this->createForm(CaseEventEditType::class, $caseEvent, ['view_timezone' => $this->serviceOptions['view_timezone']]);
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
            'case_event' => $caseEvent,
            'form' => $form->createView(),
        ]);
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('view_timezone')
        ;
    }
}
