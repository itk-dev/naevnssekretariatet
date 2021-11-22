<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class InspectionLetterType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recipients', null, [
                'label' => $this->translator->trans('Select recipients', [], 'agenda'),
            ])
            ->add('subject', TextType::class, [
                'label' => $this->translator->trans('Subject', [], 'agenda'),
            ])
            ->add('template', ChoiceType::class, [
                'label' => $this->translator->trans('Template', [], 'agenda'),
                'choices' => [
                    'Inspection letter template 1' => 'inspection_letter_1',
                    'Inspection letter template 2' => 'inspection_letter_2',
                    'Inspection letter template 3' => 'inspection_letter_3',
                ],
            ])
            ->add('contents', TextareaType::class, [
                'label' => $this->translator->trans('Contents', [], 'agenda'),
            ])
            ->add('documents', FileType::class, [
                'label' => $this->translator->trans('Attach document(s)', [], 'agenda'),
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'application/msword',
                            'application/vnd.ms-excel',
                            'text/plain',
                            'image/jpeg',
                            'image/png',
                            'video/mp4',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid document',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Send inspection letter', [], 'agenda'),
            ]);
    }
}
