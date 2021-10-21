<?php

namespace App\Form;

use App\Entity\Municipality;
use App\Service\AgendaStatus;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaFilterType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Municipality $municipality */
        $municipality = $options['municipality'];

        $boards = [];

        foreach ($municipality->getBoards()->toArray() as $board) {
            $boards[$board->getName()] = $board;
        }

        $builder
            ->add('board', Filters\ChoiceFilterType::class, [
                'choices' => $boards,
                'label' => false,
                'placeholder' => $this->translator->trans('All boards', [], 'agenda'),
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $paramName = sprintf('p_%s', str_replace('.', '_', $field));

                    // expression that represent the condition
                    $expression = $filterQuery->getExpr()->eq($field, ':'.$paramName);

                    // expression parameters
                    // Added ->getId()->toBinary() to handle Uuid
                    $parameters = [$paramName => $values['value']->getId()->toBinary()]; // [ name => value ]

                    return $filterQuery->createCondition($expression, $parameters);
                },
            ])
            ->add('date', Filters\DateFilterType::class, [
                'label' => false,
                'widget' => 'single_text',
                'input_format' => 'dd-MM-yyyy',
            ])
            ->add('status', Filters\ChoiceFilterType::class, [
                'choices' => [
                    $this->translator->trans('Open', [], 'agenda') => AgendaStatus::Open,
                    $this->translator->trans('Full', [], 'agenda') => AgendaStatus::Full,
                    $this->translator->trans('Finished', [], 'agenda') => AgendaStatus::Finished,
                    $this->translator->trans('Not-closed', [], 'agenda') => AgendaStatus::Not_closed,
                ],
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $paramName = sprintf('p_%s', str_replace('.', '_', $field));

                    // Handle not finished separately
                    if (AgendaStatus::Not_closed === $values['value']) {
                        $expression = $filterQuery->getExpr()->neq($field, ':'.$paramName);
                        $parameters = [$paramName => AgendaStatus::Finished];
                    } else {
                        $expression = $filterQuery->getExpr()->eq($field, ':'.$paramName);
                        $parameters = [$paramName => $values['value']];
                    }

                    return $filterQuery->createCondition($expression, $parameters);
                },
                'label' => false,
                'placeholder' => $this->translator->trans('All statuses', [], 'agenda'),
            ]);
    }
}
