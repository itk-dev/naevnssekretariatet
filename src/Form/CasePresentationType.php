<?php

namespace App\Form;

use App\Entity\CasePresentation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CasePresentationType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CasePresentation::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('presentation', TextareaType::class, [
                'label' => $this->translator->trans('Presentation', [], 'agenda_item'),
                'attr' => ['rows' => 6],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Update presentation', [], 'agenda_item'),
            ]);
    }
}
