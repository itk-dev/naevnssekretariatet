<?php

namespace App\Validator;

use App\Entity\Embeddable\Identification;
use App\Service\IdentifierChoices;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class PartyIdentificationValidator extends ConstraintValidator
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\PartyIdentification */

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Identification) {
            return;
        }

        $identifier = $value->getIdentifier();

        if (IdentifierChoices::IDENTIFIER_TYPE_CHOICES['CPR'] === $value->getType()) {
            // Ensure identifier looks like a CPR number
            if (!preg_match('/^\d{10}$/', $identifier, $matches)) {
                $message = $this->translator->trans('CPR number must contain 10 digits', [], 'validator');
                $this->context->buildViolation($message)
                    ->addViolation()
                ;
            }
        } else {
            // Ensure identifier looks like a CVR number
            if (!preg_match('/^\d{8}$/', $identifier, $matches)) {
                $message = $this->translator->trans('CVR number must contain 8 digits', [], 'validator');
                $this->context->buildViolation($message)
                    ->addViolation()
                ;
            }
        }
    }
}
