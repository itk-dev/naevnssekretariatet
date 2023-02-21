<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\Municipality;
use App\Entity\User;
use App\Repository\BoardRepository;
use App\Repository\CaseEntityRepository;
use App\Repository\UserRepository;
use App\Service\AgendaStatus;
use App\Service\CaseDeadlineStatuses;
use App\Service\CaseSpecialFilterStatuses;
use App\Service\FilterHelper;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseFilterType extends AbstractType
{
    public function __construct(private readonly BoardRepository $boardRepository, private readonly CaseEntityRepository $caseEntityRepository, private readonly FilterHelper $filterHelper, private readonly TranslatorInterface $translator, private readonly UserRepository $userRepository)
    {
    }

    public function getBlockPrefix()
    {
        return 'case_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => ['filtering'],
            'municipality' => null,
            'isBoardMember' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Municipality $municipality */
        $municipality = $options['municipality'];
        $isBoardMember = $options['isBoardMember'];

        $builder
            ->add('board', Filters\ChoiceFilterType::class, [
                'choices' => $this->boardRepository->createQueryBuilder('b')
                    ->indexBy('b', 'b.name')
                    ->where('b.municipality = :municipality')
                    ->setParameter('municipality', $municipality->getId()->toBinary())
                    ->orderBy('b.name', 'ASC')
                    ->getQuery()->getResult(),
                'label' => false,
                // Use ID as choice value
                'choice_value' => function (?Board $board) {
                    return $board ? $board->getId() : '';
                },
                'placeholder' => $this->translator->trans('All boards', [], 'case'),
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    return $this->filterHelper->applyFilterWithUuids($filterQuery, $field, $values);
                },
            ])
        ;

        // Dynamically show list of statuses via board
        $formModifier = function (FormInterface $form, Board $board = null) {
            if (null != $board) {
                // Retrieve list of statuses by board and make them into options
                $statuses = array_filter(array_map('trim', explode(PHP_EOL, $board->getStatuses())));
                $statusOptions = [];

                foreach ($statuses as $status) {
                    $statusOptions[$status] = $status;
                }

                $form->add('currentPlace', Filters\ChoiceFilterType::class, [
                    'choices' => $statusOptions,
                    'label' => false,
                    'placeholder' => $this->translator->trans('All statuses', [], 'case'),
                ]);
            } else {
                $form->add('currentPlace', HiddenType::class);
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event->getForm(), $event->getData());
            }
        );

        $builder->get('board')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $board = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), $board);
            }
        );

        $potentialCaseworkers = $this->userRepository->findByRole('ROLE_CASEWORKER', ['name' => 'ASC']);

        $correctedCaseworkers = [];

        foreach ($potentialCaseworkers as $user) {
            $correctedCaseworkers[$user->getName()] = $user;
        }

        if (!$isBoardMember) {
            $builder->add('assignedTo', Filters\ChoiceFilterType::class, [
                'choices' => $correctedCaseworkers,
                'label' => false,
                // Use ID as choice value
                'choice_value' => function (?User $user) {
                    return $user ? $user->getId() : '';
                },
                'placeholder' => $this->translator->trans('All caseworkers', [], 'case'),
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    return $this->filterHelper->applyFilterWithUuids($filterQuery, $field, $values);
                },
            ])
            ;

            $builder
                ->add('deadlines', Filters\ChoiceFilterType::class, [
                    'choices' => [
                        $this->translator->trans('Exceeded hearing response deadline', [], 'case') => CaseDeadlineStatuses::HEARING_RESPONSE_DEADLINE_EXCEEDED,
                        $this->translator->trans('Exceeded hearing deadline', [], 'case') => CaseDeadlineStatuses::HEARING_DEADLINE_EXCEEDED,
                        $this->translator->trans('Exceeded processing deadline', [], 'case') => CaseDeadlineStatuses::PROCESS_DEADLINE_EXCEEDED,
                        $this->translator->trans('Some deadline exceeded', [], 'case') => CaseDeadlineStatuses::SOME_DEADLINE_EXCEEDED,
                        $this->translator->trans('All deadlines exceeded', [], 'case') => CaseDeadlineStatuses::ALL_DEADLINES_EXCEEDED,
                        $this->translator->trans('No exceeded deadlines', [], 'case') => CaseDeadlineStatuses::NO_DEADLINES_EXCEEDED,
                    ],
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $filterChoice = $values['value'];

                        // Base expression and parameters
                        // If filter choice is some (one or more) exceeded we need OR rather than AND
                        $resultExpression = CaseDeadlineStatuses::SOME_DEADLINE_EXCEEDED === $filterChoice ? $filterQuery->getExpr()->orX() : $filterQuery->getExpr()->andX();
                        $parameters = [];

                        // Add one or two expressions based on filter choice aka. $values['value']
                        // Filters that require one expression are hearing, hearing response and process deadline exceeded
                        // All exceeded, none exceeded and some (one or more) exceeded require three expressions, one for hearing, one for hearing response and one for process deadline exceeded

                        // Iterate over three statuses that will define the expressions needed
                        foreach ([CaseDeadlineStatuses::HEARING_RESPONSE_DEADLINE_EXCEEDED, CaseDeadlineStatuses::HEARING_DEADLINE_EXCEEDED, CaseDeadlineStatuses::PROCESS_DEADLINE_EXCEEDED] as $iteratorStatus) {
                            // Check if filter choice is status iterator or one of the filtering choices that need both expressions
                            if (in_array($filterChoice, [$iteratorStatus, CaseDeadlineStatuses::ALL_DEADLINES_EXCEEDED, CaseDeadlineStatuses::NO_DEADLINES_EXCEEDED, CaseDeadlineStatuses::SOME_DEADLINE_EXCEEDED])) {
                                // Construct expression depending on status iterator
                                $field = match ($iteratorStatus) {
                                    CaseDeadlineStatuses::HEARING_RESPONSE_DEADLINE_EXCEEDED => 'c.hasReachedHearingResponseDeadline',
                                    CaseDeadlineStatuses::HEARING_DEADLINE_EXCEEDED => 'c.hasReachedHearingDeadline',
                                    CaseDeadlineStatuses::PROCESS_DEADLINE_EXCEEDED => 'c.hasReachedProcessingDeadline',
                                };
                                $paramName = sprintf('p_%s', str_replace('.', '_', $field));
                                $expression = $filterQuery->getExpr()->eq($field, ':'.$paramName);
                                $resultExpression->add($expression);
                                $parameters[$paramName] = CaseDeadlineStatuses::NO_DEADLINES_EXCEEDED !== $filterChoice;
                            }
                        }

                        return $filterQuery->createCondition($resultExpression, $parameters);
                    },
                    'label' => false,
                    'placeholder' => $this->translator->trans('Select deadline filter', [], 'case'),
                ])
            ;

            $builder
                ->add('specialStateFilter', Filters\ChoiceFilterType::class, [
                    'choices' => [
                        $this->translator->trans('In hearing', [], 'case') => CaseSpecialFilterStatuses::IN_HEARING,
                        $this->translator->trans('New hearing post', [], 'case') => CaseSpecialFilterStatuses::NEW_HEARING_POST,
                        $this->translator->trans('On agenda', [], 'case') => CaseSpecialFilterStatuses::ON_AGENDA,
                    ],
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $filterChoice = $values['value'];

                        // Modify query builder according to filter choice
                        switch ($filterChoice) {
                            case CaseSpecialFilterStatuses::IN_HEARING:
                                $qb = $filterQuery->getQueryBuilder();
                                $qb->join('c.hearing', 'h')
                                    ->where('h.startedOn IS NOT NULL')
                                    ->andWhere('h.finishedOn IS NULL')
                                ;
                                break;
                            case CaseSpecialFilterStatuses::NEW_HEARING_POST:
                                $qb = $filterQuery->getQueryBuilder();
                                $qb->join('c.hearing', 'h')
                                    ->where('h.hasNewHearingPost = 1')
                                ;
                                break;
                            case CaseSpecialFilterStatuses::ON_AGENDA:
                                $qb = $filterQuery->getQueryBuilder();
                                $qb->leftJoin('c.agendaCaseItems', 'aci')
                                    ->join('aci.agenda', 'a')
                                    ->where('a.status != :agenda_status')
                                    ->setParameter('agenda_status', AgendaStatus::FINISHED)
                                ;
                                break;
                        }

                        return $filterQuery->createCondition($filterQuery->getExpr()->andX(), []);
                    },
                    'label' => false,
                    'placeholder' => $this->translator->trans('Select a special filter', [], 'case'),
                ])
            ;
        }

        $builder->add('activeFilter', Filters\ChoiceFilterType::class, [
            'choices' => [
                $this->translator->trans('Active', [], 'case') => CaseSpecialFilterStatuses::ACTIVE,
                $this->translator->trans('Not active', [], 'case') => CaseSpecialFilterStatuses::NOT_ACTIVE,
            ],
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                if (empty($values['value'])) {
                    return null;
                }

                $filterChoice = $values['value'];

                // Base expression and parameters
                $expression = $filterQuery->getExpr()->orX();
                $parameters = [];

                $boardRepository = $this->boardRepository;
                $boards = $boardRepository->findAll();

                $count = 0;
                foreach ($boards as $board) {
                    $rawPlaces = explode(
                        PHP_EOL,
                        trim($board->getStatuses())
                    );

                    $finishedStatus = trim(end($rawPlaces));

                    // Construct different variable names for each board
                    $statusDQLVariable = 'board_finish_status_'.$count;
                    $boardDQLVariable = 'board_'.$count;

                    $expression->add($filterQuery->getExpr()->andX(
                        CaseSpecialFilterStatuses::ACTIVE === $filterChoice
                            ? $filterQuery->getExpr()->neq('c.currentPlace', ':'.$statusDQLVariable)
                            : $filterQuery->getExpr()->eq('c.currentPlace', ':'.$statusDQLVariable),
                        $filterQuery->getExpr()->eq('c.board', ':'.$boardDQLVariable),
                    ));

                    $parameters[$statusDQLVariable] = $finishedStatus;
                    $parameters[$boardDQLVariable] = $board->getId()->toBinary();

                    ++$count;
                }

                return $filterQuery->createCondition($expression, $parameters);
            },
            'label' => false,
            'placeholder' => $this->translator->trans('Select a general status filter', [], 'case'),
        ]);
    }
}
