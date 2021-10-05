<?php

namespace App\Form;

use App\Entity\CasePresentation;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('presentation', CKEditorType::class, [
                'required' => false,
                'label' => $this->translator->trans('Presentation', [], 'agenda_item'),
                'attr' => ['rows' => 6],
                'config' => [
                    'uiColor' => '#ffffff',
                    'toolbar' => 'editor',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Update presentation', [], 'agenda_item'),
            ]);
    }
}
