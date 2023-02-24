<?php

namespace App\Form;

use App\Entity\CaseEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseEventEditType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CaseEvent::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CaseEvent $caseEvent */
        $caseEvent = $builder->getData();

        $builder
            ->add('subject', TextType::class, [
                'label' => $this->translator->trans('Subject', [], 'case_event'),
            ])
            ->add('receivedAt', DateTimeType::class, [
                'label' => $this->translator->trans('Received at', [], 'case_event'),
                'widget' => 'single_text',
            ])
        ;

        if (CaseEvent::CATEGORY_NOTE === $caseEvent->getCategory()) {
            $builder->add('noteContent', TextareaType::class, [
                'label' => $this->translator->trans('Note content', [], 'case_event'),
                'attr' => ['rows' => 6],
            ]);
        }
    }
}
