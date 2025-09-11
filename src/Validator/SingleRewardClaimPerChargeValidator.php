<?php

namespace App\Validator;

use App\Dto\RewardClaimCreationDto;
use App\Repository\Project\RewardClaimRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class SingleRewardClaimPerChargeValidator extends ConstraintValidator
{
    private function __construct(
        private RewardClaimRepository $rewardClaimRepository,
    ) {}

    /** @param RewardClaimCreationDto $value */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $claim = $this->rewardClaimRepository->findOneBy([
            'charge' => $value->charge->id,
        ]);

        if ($claim === null) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
