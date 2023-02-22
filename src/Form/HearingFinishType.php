<?php

namespace App\Form;

use App\Entity\FenceReviewCase;
use App\Entity\Hearing;
use App\Entity\RentBoardCase;
use App\Entity\ResidentComplaintBoardCase;
use App\Exception\CaseClassException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class HearingFinishType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hearing::class,
            'case' => null,
            'hasCounterparty' => null,
            'hasParty' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $case = $options['case'];

        switch ($case::class) {
            case ResidentComplaintBoardCase::class:
            case RentBoardCase::class:
                $partyLabel = new TranslatableMessage('Tenant side has no more to add', [], 'case');
                $counterPartyLabel = new TranslatableMessage('Landlord side has no more to add', [], 'case');
                break;
            case FenceReviewCase::class:
                $partyLabel = new TranslatableMessage('Bringer side has no more to add', [], 'case');
                $counterPartyLabel = new TranslatableMessage('Neighbour side has no more to add', [], 'case');
                break;
            default:
                $message = sprintf('Case class %s not handled.', $case::class);
                throw new CaseClassException($message);
        }

        if ($options['hasParty']) {
            $builder->add('partyHasNoMoreToAdd', CheckboxType::class, [
                'label' => $partyLabel,
                'required' => false,
            ]);
        }

        if ($options['hasCounterparty']) {
            $builder->add('counterpartHasNoMoreToAdd', CheckboxType::class, [
                'label' => $counterPartyLabel,
                'required' => false,
            ]);
        }
    }
}
