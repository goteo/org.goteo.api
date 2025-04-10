<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class EnoughRewardCharge extends Constraint
{
    public string $message = 'The charged money is not enough to claim the ProjectReward.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
