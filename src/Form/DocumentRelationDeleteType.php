<?php

namespace App\Form;

use App\Entity\CaseDocumentRelation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentRelationDeleteType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CaseDocumentRelation::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('removalReason', TextareaType::class, [
                'label' => $this->translator->trans('Reason', [], 'documents'),
                'help' => $this->translator->trans('Please provide a reason for deletion of the document', [], 'documents'),
                'attr' => [
                    'rows' => 6,
                ],
            ])
        ;
    }
}
