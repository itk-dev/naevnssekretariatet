<?php

namespace App\Validator;

use App\Entity\Embeddable\Identification;
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

        // Ensure identifier looks like a CPR or CVR number
        if (!preg_match('/^(\d{6}-?\d{4}|\d{8})$/', $value->getIdentifier(), $matches)) {
            $message = $this->translator->trans('The value { value } is not a valid CPR or CVR number.', ['{ value }' => $value->getIdentifier()], 'validator');
            $this->context->buildViolation($message)
                ->addViolation()
            ;
        }
    }
}
