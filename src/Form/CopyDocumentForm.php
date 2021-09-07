<?php

namespace App\Form;

use App\Entity\CaseEntity;
use App\Entity\ResidentComplaintBoardCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CopyDocumentForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'case' => null,
            'suitableCases' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CaseEntity $case */
        $case = $options['case'];
        $suitableCases = $options['suitableCases'];

        $builder->add('cases', EntityType::class, [
            'choices' => $suitableCases,
            'class' => get_class($case),
            'label' => 'Copy to',
            'multiple' => true,
            'attr' => [
                'size' => 10,
            ],
        ]);
    }
}
