<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\ComplaintCategory;
use App\Entity\ResidentComplaintBoardCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResidentComplaintBoardCaseType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResidentComplaintBoardCase::class,
            'board' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Board $board */
        $board = $options['board'];

        $builder
            ->add('complainant', TextType::class, [
                'label' => $this->translator->trans('Complainant', [], 'case'),
            ])
            ->add('complainantPhone', IntegerType::class, [
                'label' => $this->translator->trans('Complainant phone', [], 'case'),
            ])
            ->add('complainantAddress', TextType::class, [
                'label' => $this->translator->trans('Complainant address', [], 'case'),
            ])
            ->add('complainantPostalCode', TextType::class, [
                'label' => $this->translator->trans('Complainant postal code', [], 'case'),
            ])
            ->add('complaintCategory', EntityType::class, [
                'class' => ComplaintCategory::class,
                'choices' => $board->getComplaintCategories(),
                'label' => $this->translator->trans('Complaint category', [], 'case'),
                'placeholder' => $this->translator->trans('Select a complaint category', [], 'case'),
            ])
            ->add('size', IntegerType::class, [
                'label' => $this->translator->trans('Size', [], 'case'),
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->translator->trans('Create Case', [], 'case'),
                'attr' => ['class' => 'btn btn-success float-right'],
            ])
        ;
    }
}
