<?php

namespace App\Form;

use App\Entity\ComplaintCategory;
use App\Entity\ResidentComplaintBoardCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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

        $builder
            ->add('complainant')
            ->add('complainantPhone')
            ->add('complainantAddress')
            ->add('complainantPostalCode')
            ->add('complaintCategory', EntityType::class, [
                'class' => ComplaintCategory::class,
                'choices' => [
                    'Complaint Categories' => $caseTypes,
                ],
                'choice_label' => 'name',
            ])
            ->add('save', SubmitType::class)
        ;
    }
}
