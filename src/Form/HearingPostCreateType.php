<?php

namespace App\Form;

use App\Entity\HearingPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class HearingPostCreateType extends AbstractType
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
            'data_class' => HearingPost::class,
            'case_parties' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $case_parties = $options['case_parties'];

        $builder
            ->add('sender', ChoiceType::class, [
                'label' => $this->translator->trans('Sender', [], 'case'),
                'choices' => $case_parties,
            ])
            ->add('recipient', ChoiceType::class, [
                'label' => $this->translator->trans('Recipient', [], 'case'),
                'choices' => $case_parties,
            ])
            ->add('content', TextareaType::class, [
                'label' => $this->translator->trans('Content', [], 'case'),
            ])
        ;
    }
}
