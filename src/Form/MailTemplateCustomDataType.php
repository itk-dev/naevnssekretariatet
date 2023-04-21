<?php

namespace App\Form;

use App\Service\MailTemplateHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailTemplateCustomDataType extends AbstractType
{
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_TEXT = 'text';

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

            foreach ($customFields as $key => $value) {
                $type = self::TYPE_TEXTAREA === $value['type'] ? TextareaType::class : TextType::class;

                $typeOptions = [
                    'label' => $value['label'],
                    'mapped' => false,
                    'data' => isset($options['data'][$key]) && !empty($options['data'][$key]) ? $options['data'][$key] : '',
                    'required' => false,
                ];

                if (self::TYPE_TEXTAREA === $value['type']) {
                    $typeOptions['attr'] = ['rows' => 5];
                }

                $builder->add($key, $type, $typeOptions);
            }
        }
    }
}
