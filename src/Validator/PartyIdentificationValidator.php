<?php

namespace App\Validator;

use App\Entity\Embeddable\Identification;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PartyIdentificationValidator extends ConstraintValidator
{
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
        if (!preg_match('/^\d{6}\-?\d{4}$|^\d{8}$/', $value->getIdentifier(), $matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value->getIdentifier())
                ->addViolation()
            ;
        }
    }
}
