<?php

namespace App\Matchfunding\Rule;

use App\Entity\Gateway\Charge;
use App\Entity\Project\Project;
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

    public function validate(Charge $charge, Project $project): bool
    {
        $charges = $this->chargeRepository->findBy([
            'checkout.origin' => $charge->getCheckout()->getOrigin(),
            'target' => $project->getAccounting(),
        ]);

        if (\count($charges) === 0) {
            return true;
        }

        return false;
    }
}
