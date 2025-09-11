<?php

namespace App\Validator;

use App\ApiResource\Project\RewardClaimApiResource;
use App\Money\MoneyService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class EnoughRewardChargeValidator extends ConstraintValidator
{
    public function __construct(
        private MoneyService $money,
    ) {}

    /**
     * @param RewardClaimApiResource $value
     * @param EnoughRewardCharge     $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $charge = $value->charge->money;
        $reward = $value->reward->money;

        if ($this->money->isMoreOrSame($charge, $reward)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
