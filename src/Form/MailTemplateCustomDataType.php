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
    final public const TYPE_TEXTAREA = 'textarea';
    final public const TYPE_TEXT = 'text';

    public function __construct(private readonly MailTemplateHelper $mailTemplateHelper)
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

                $options = [
                    'label' => $value['label'],
                    'mapped' => false,
                    'data' => isset($options['data'][$key]) && !empty($options['data'][$key]) ? $options['data'][$key] : '',
                    'required' => false,
                ];

                if (self::TYPE_TEXTAREA === $value['type']) {
                    $options['attr'] = ['rows' => 5];
                }

                $builder->add($key, $type, $options);
            }
        }
    }
}
