<?php

namespace App\Mapping\Transformer;

use App\ApiResource\Project\Budget;
use App\ApiResource\Project\BudgetSummary;
use App\Entity\Money;
use App\Entity\Project\BudgetItem;
use App\Entity\Project\BudgetItemType;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectDeadline;
use App\Library\Economy\MoneyService;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerInterface;

class BudgetMapTransformer implements PropertyTransformerInterface
{
    public function __construct(
        private MoneyService $moneyService,
    ) {}

    /**
     * @param Project $source
     */
    public function transform(mixed $value, object|array $source, array $context): mixed
    {
        $items = $source->getBudgetItems()->toArray();
        $currency = $source->getAccounting()->getCurrency();

        $budget = new Budget();
        $budget->minimum = $this->getItemsSummary($items, $currency, ProjectDeadline::Minimum);
        $budget->optimum = $this->getItemsSummary($items, $currency, ProjectDeadline::Optimum);

        return $budget;
    }

    private function filterItemsByType(array $items, BudgetItemType $type)
    {
        return \array_filter($items, function (BudgetItem $item) use ($type) {
            return $item->getType() === $type;
        });
    }

    /**
     * @param BudgetItem[] $items
     *
     * @return array{minimum: Money, optimum: Money}
     */
    private function calcItemsTotal(array $items, string $currency): array
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

    private function getItemsSummary(array $items, string $currency, ProjectDeadline $deadline)
    {
        $deadlineValue = $deadline->value;

        $summary = new BudgetSummary();

        $summary->money = $this->calcItemsTotal($items, $currency)[$deadlineValue];

        $summary->task = $this->calcItemsTotal(
            $this->filterItemsByType(
                $items,
                BudgetItemType::Task
            ),
            $currency
        )[$deadlineValue];

        $summary->material = $this->calcItemsTotal(
            $this->filterItemsByType(
                $items,
                BudgetItemType::Material
            ),
            $currency
        )[$deadlineValue];

        $summary->infra = $this->calcItemsTotal(
            $this->filterItemsByType(
                $items,
                BudgetItemType::Infrastructure
            ),
            $currency
        )[$deadlineValue];

        return $summary;
    }
}
