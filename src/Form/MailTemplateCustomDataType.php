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
            $customFields = $this->mailTemplateHelper->computeMergeFields($options['template']);

            foreach ($customFields as $customField) {
                $builder->add($customField, TextType::class, [
                    'label' => $customField,
                    'mapped' => false,
                    'data' => isset($options['data'][$customField]) && !empty($options['data'][$customField]) ? $options['data'][$customField] : '',
                    'required' => false,
                ]);
            }
        }
    }
}
