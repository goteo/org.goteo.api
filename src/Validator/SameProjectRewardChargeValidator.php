<?php

namespace App\Validator;

use App\ApiResource\Project\RewardClaimApiResource;
use App\Entity\Project\Project;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class SameProjectRewardChargeValidator extends ConstraintValidator
{
    /**
     * @param RewardClaimApiResource $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $charge = $value->charge;
        $reward = $value->reward;

        if (
            $charge->target->ownerObject instanceof Project
            && $charge->target->ownerObject?->getId() === $reward->project->id
        ) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
