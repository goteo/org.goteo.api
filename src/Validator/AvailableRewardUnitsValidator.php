<?php

namespace App\Validator;

use App\ApiResource\Project\RewardApiResource;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class AvailableRewardUnitsValidator extends ConstraintValidator
{
    /**
     * @param RewardApiResource $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value->hasUnits) {
            return;
        }

        if ($value->hasUnits && $value->unitsAvailable > 0) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ id }}', $value->id)
            ->addViolation()
        ;
    }
}
