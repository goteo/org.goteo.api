<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class SameProjectRewardCharge extends Constraint
{
    public string $message = 'The Project of the Reward is not the same as the Project targeted in the Charge.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
