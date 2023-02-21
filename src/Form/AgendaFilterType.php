<?php

namespace App\Form;

use App\Entity\Municipality;
use App\Repository\BoardRepository;
use App\Service\AgendaStatus;
use App\Service\FilterHelper;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaFilterType extends AbstractType
{
    public function __construct(private readonly BoardRepository $boardRepository, private readonly FilterHelper $filterHelper, private readonly TranslatorInterface $translator)
    {
    }

    public function getBlockPrefix()
    {
        return 'agenda_filter';
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

        // Modify status filter choices if board member
        $statusChoices = $isBoardMember
            ? [
                $this->translator->trans('Finished', [], 'agenda') => AgendaStatus::FINISHED,
                $this->translator->trans('Open', [], 'agenda') => AgendaStatus::NOT_FINISHED,
            ]
            : [
                $this->translator->trans('Open', [], 'agenda') => AgendaStatus::OPEN,
                $this->translator->trans('Full', [], 'agenda') => AgendaStatus::FULL,
                $this->translator->trans('Ready', [], 'agenda') => AgendaStatus::READY,
                $this->translator->trans('Finished', [], 'agenda') => AgendaStatus::FINISHED,
                $this->translator->trans('Not-closed', [], 'agenda') => AgendaStatus::NOT_FINISHED,
            ];

        $builder
            ->add('board', Filters\ChoiceFilterType::class, [
                'choices' => $this->boardRepository->createQueryBuilder('b')
                    ->indexBy('b', 'b.name')
                    ->where('b.municipality = :municipality')
                    ->setParameter('municipality', $municipality->getId()->toBinary())
                    ->orderBy('b.name', 'ASC')
                    ->getQuery()->getResult(),
                'label' => false,
                'placeholder' => $this->translator->trans('All boards', [], 'agenda'),
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    return $this->filterHelper->applyFilterWithUuids($filterQuery, $field, $values);
                },
            ])
            ->add('date', Filters\DateFilterType::class, [
                'label' => false,
                'widget' => 'single_text',
                'input_format' => 'dd-MM-yyyy',
            ])
            ->add('status', Filters\ChoiceFilterType::class, [
                'choices' => $statusChoices,
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $paramName = sprintf('p_%s', str_replace('.', '_', $field));

                    // Handle not finished separately
                    if (AgendaStatus::NOT_FINISHED === $values['value']) {
                        $expression = $filterQuery->getExpr()->neq($field, ':'.$paramName);
                        $parameters = [$paramName => AgendaStatus::FINISHED];
                    } else {
                        $expression = $filterQuery->getExpr()->eq($field, ':'.$paramName);
                        $parameters = [$paramName => $values['value']];
                    }

                    return $filterQuery->createCondition($expression, $parameters);
                },
                'label' => false,
                'placeholder' => $this->translator->trans('All statuses', [], 'agenda'),
            ])
        ;
    }
}
