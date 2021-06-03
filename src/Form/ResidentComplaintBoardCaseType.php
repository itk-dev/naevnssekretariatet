<?php

namespace App\Form;

use App\Entity\ResidentComplaintBoardCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResidentComplaintBoardCaseType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResidentComplaintBoardCase::class,
            'board' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var $caseTypes array
         */
        $caseTypes = $options['board']->getComplaintCategories()->toArray();

        $caseTypesAssociative = [];

        // Make array contain strings (names) rather then the objects
        foreach ($caseTypes as $value) {
            $name = $value->getName();
            $caseTypesAssociative[$name] = $name;
        }

        $builder
            ->add('complainant')
            ->add('complainantPhone')
            ->add('complainantAddress')
            ->add('complainantPostalCode')
            ->add('caseType', ChoiceType::class, [
                'choices' => [
                    $caseTypesAssociative,
                ],
            ])
            ->add('size')
            ->add('createCase', SubmitType::class, ['label' => 'Create case'])
        ;
    }
}
