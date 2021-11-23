<?php

namespace App\Form;

use App\Entity\AgendaProtocol;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaProtocolType extends AbstractType
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
            'data_class' => AgendaProtocol::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('protocol', CKEditorType::class, [
                'label' => $this->translator->trans('Agenda protocol', [], 'agenda'),
                'attr' => ['rows' => 6],
                'config' => [
                    'uiColor' => '#ffffff',
                    'toolbar' => 'editor',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Update protocol', [], 'agenda'),
            ])
        ;
    }
}
