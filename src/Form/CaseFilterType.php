<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\Municipality;
use App\Repository\BoardRepository;
use App\Repository\UserRepository;
use App\Service\BoardHelper;
use App\Service\FilterHelper;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseFilterType extends AbstractType
{
    /**
     * @var BoardHelper
     */
    private $boardHelper;
    /**
     * @var BoardRepository
     */
    private $boardRepository;
    /**
     * @var FilterHelper
     */
    private $filterHelper;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(BoardHelper $boardHelper, BoardRepository $boardRepository, FilterHelper $filterHelper, TranslatorInterface $translator, UserRepository $userRepository)
    {
        $this->boardHelper = $boardHelper;
        $this->boardRepository = $boardRepository;
        $this->filterHelper = $filterHelper;
        $this->translator = $translator;
        $this->userRepository = $userRepository;
    }

    public function getBlockPrefix()
    {
        return 'case_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => ['filtering'],
            'municipality' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Municipality $municipality */
        $municipality = $options['municipality'];

        $builder
            ->add('board', Filters\ChoiceFilterType::class, [
                'choices' => $this->boardRepository->createQueryBuilder('b')
                    ->indexBy('b', 'b.name')
                    ->where('b.municipality = :municipality')
                    ->setParameter('municipality', $municipality->getId()->toBinary())
                    ->orderBy('b.name', 'ASC')
                    ->getQuery()->getResult(),
                'label' => false,
                'placeholder' => $this->translator->trans('All boards', [], 'agenda'),
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    return $this->filterHelper->applyFilterWithUuids($filterQuery, $field, $values);
                },
            ])
        ;

        // Dynamically show list of statuses via board
        $formModifier = function (FormInterface $form, Board $board = null) {
            if (null != $board) {
                $form->add('currentPlace', Filters\ChoiceFilterType::class, [
                    'choices' => $this->boardHelper->getStatusesByBoard($board),
                    'label' => false,
                    'placeholder' => $this->translator->trans('All statuses', [], 'case'),
                ]);
            } else {
                $form->add('currentPlace', HiddenType::class);
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event->getForm(), $event->getData());
            }
        );

        $builder->get('board')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $board = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), $board);
            }
        );

        $potentialCaseworkers = $this->userRepository->findByRole('ROLE_CASEWORKER', ['name' => 'ASC']);

        $correctedCaseworkers = [];

        foreach ($potentialCaseworkers as $user) {
            $correctedCaseworkers[$user->getName()] = $user;
        }

        $builder->add('assignedTo', Filters\ChoiceFilterType::class, [
            'choices' => $correctedCaseworkers,
            'label' => false,
            'placeholder' => $this->translator->trans('All caseworkers', [], 'agenda'),
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                return $this->filterHelper->applyFilterWithUuids($filterQuery, $field, $values);
            },
        ])
        ;
    }
}
