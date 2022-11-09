<?php

namespace App\Form;

use App\Service\MailTemplateHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailTemplateCustomDataType extends AbstractType
{
    public function __construct(private MailTemplateHelper $mailTemplateHelper)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'template' => null,
            'data' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (null !== $options['template']) {
            $customFields = $this->mailTemplateHelper->getCustomFields($options['template']);

            foreach ($customFields as $key => $label) {
                $builder->add($key, TextType::class, [
                    'label' => $label,
                    'mapped' => false,
                    'data' => isset($options['data'][$key]) && !empty($options['data'][$key]) ? $options['data'][$key] : '',
                    'required' => false,
                ]);
            }
        }
    }
}
