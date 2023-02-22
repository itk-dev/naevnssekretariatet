<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\Municipality;
use App\Repository\BoardRepository;
use App\Repository\MunicipalityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewCaseMunicipalityAndBoardType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly BoardRepository $boardRepository, private readonly MunicipalityRepository $municipalityRepository)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'active_municipality' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $activeMunicipality = $options['active_municipality'];

        $builder->add('municipality', EntityType::class, [
            'class' => Municipality::class,
            'choices' => $this->municipalityRepository->findBy([], ['name' => 'ASC']),
            'data' => $activeMunicipality,
            'label' => $this->translator->trans('Municipality', [], 'case'),
            'placeholder' => $this->translator->trans('Choose a municipality', [], 'case'),
        ]);

        // Adds or hides form children based on provided municipalityId
        $addBoardFormModifier = function (FormInterface $form, $municipalityId = null) {
            if (null != $municipalityId) {
                // No municipality chosen - show board form child
                $boardChoices = $this->boardRepository->findBy(['municipality' => $municipalityId], ['name' => 'ASC']);

                $form->add('board', EntityType::class, [
                    'class' => Board::class,
                    'choice_label' => 'name',
                    'choices' => $boardChoices,
                    'placeholder' => $this->translator->trans('Choose a board', [], 'case'),
                ]);
            } else {
                // No municipality chosen - hide board and caseEntity form children
                $form->add('board', HiddenType::class);
            }
        };

        // Base event listener for adding board child to form
        // @see https://symfony.com/doc/5.4/form/dynamic_form_modification.html#dynamic-generation-for-submitted-forms
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($addBoardFormModifier) {
                $data = $event->getData();
                $municipalityId = $data['municipality'] ?? null;
                $addBoardFormModifier($event->getForm(), $municipalityId);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($addBoardFormModifier) {
                $data = $event->getData();
                $municipalityId = $data['municipality'] ?? null;
                $addBoardFormModifier($event->getForm(), $municipalityId);
            }
        );
    }
}
