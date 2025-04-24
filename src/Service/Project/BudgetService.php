<?php

namespace App\Service\Project;

use App\Entity\Money;
use App\Entity\Project\BudgetItem;
use App\Entity\Project\ProjectDeadline;
use App\Library\Economy\MoneyService;

class BudgetService
{
    public function __construct(
        private MoneyService $moneyService,
    ) {}

    /**
     * @param BudgetItem[] $items
     *
     * @return array{minimum: Money, optimum: Money}
     */
    public function calcBudget(array $items, string $currency): array
    {
        $totalMinimum = new Money(0, $currency);
        $totalOptimum = new Money(0, $currency);

        foreach ($items as $item) {
            $itemMoney = $item->getMoney();
            $itemDeadline = $item->getDeadline();

            if ($itemDeadline == ProjectDeadline::Minimum) {
                $totalMinimum = $this->moneyService->add($itemMoney, $totalMinimum);
            }

            $totalOptimum = $this->moneyService->add($itemMoney, $totalOptimum);
        }

        return [
            ProjectDeadline::Minimum->value => $totalMinimum,
            ProjectDeadline::Optimum->value => $totalOptimum,
        ];
    }
}
