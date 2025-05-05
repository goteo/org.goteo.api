<?php

namespace App\Matchfunding\Rule;

use App\Entity\Gateway\Charge;
use App\Entity\Matchfunding\MatchCallSubmission;
use App\Repository\Gateway\ChargeRepository;

class SingleUserPerProjectRule implements RuleInterface
{
    public function __construct(
        private ChargeRepository $chargeRepository,
    ) {}

    public static function getDescription(): string
    {
        return 'Validates that the matching is done only once per user per project.';
    }

    public function validate(Charge $charge, MatchCallSubmission $submission): bool
    {
        $charges = $this->chargeRepository->findByOriginAndTarget(
            $charge->getCheckout()->getOrigin(),
            $submission->getProject()->getAccounting()
        );

        if (\count($charges) === 0) {
            return true;
        }

        return false;
    }
}
