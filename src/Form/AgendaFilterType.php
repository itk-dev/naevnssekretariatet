<?php

namespace App\Form;

use App\Entity\Municipality;
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

//        var_dump($municipality);
//        die(__FILE__);

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
                    // or if you need to define the parameter's type
                    // $parameters = array($paramName => array($values['value'], \PDO::PARAM_STR)); // [ name => [value, type] ]

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
                'Open' => 'Open',
                'Full' => 'Full',
                'Finished' => 'Finished',
            ],
            'label' => false,
            'placeholder' => $this->translator->trans('All statuses', [], 'agenda'),
        ]);
    }
}
