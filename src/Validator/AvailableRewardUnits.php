<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AvailableRewardUnits extends Constraint
{
    public string $message = 'The ProjectReward "{{ id }}" has no units available.';
}
