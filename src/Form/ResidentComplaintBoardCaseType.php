<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\ComplaintCategory;
use App\Entity\ResidentComplaintBoardCase;
use App\Entity\SubBoard;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('subboard', EntityType::class, [
                'class' => SubBoard::class,
                'choices' => $board->getSubBoards(),
            ])
            ->add('complainant')
            ->add('complainantPhone')
            ->add('complainantAddress')
            ->add('complainantPostalCode')
            ->add('complaintCategory', EntityType::class, [
                'class' => ComplaintCategory::class,
                'choices' => $board->getComplaintCategories(),
            ])
            ->add('size')
            ->add('save', SubmitType::class, [
                'label' => $this->translator->trans('Create Case', [], 'case'),
                'attr' => ['class' => 'btn btn-success float-right'],
            ])
        ;
    }
}
