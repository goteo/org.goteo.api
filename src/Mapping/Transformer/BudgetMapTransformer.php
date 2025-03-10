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
    public const MINIMUM = ProjectDeadline::Minimum->value;
    public const OPTIMUM = ProjectDeadline::Optimum->value;
    public const VALID_SUMMARY_TYPES = [self::MINIMUM, self::OPTIMUM];

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
        $budget->minimum = $this->getItemsSummary($items, $currency, self::MINIMUM);
        $budget->optimum = $this->getItemsSummary($items, $currency, self::OPTIMUM);

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
        $totals = [
            self::MINIMUM => new Money(0, $currency),
            self::OPTIMUM => new Money(0, $currency),
        ];

        foreach ($items as $item) {
            $itemMoney = $item->getMoney();
            $itemDeadlineValue = $item->getDeadline()->value;

            if (isset($totals[$itemDeadlineValue])) {
                $totals[$itemDeadlineValue] = $this->moneyService->add($itemMoney, $totals[$itemDeadlineValue]);
            }
        }

        return $totals;
    }


    private function getItemsSummary(array $items, string $currency, string $type)
    {
        if (!\in_array($type, self::VALID_SUMMARY_TYPES)) {
            throw new \Exception(\sprintf('BudgetSummary must be of \'%s\' type.', \join(', ', self::VALID_SUMMARY_TYPES)));
        }

        $summary = new BudgetSummary();
        $summary->money = $this->calcItemsTotal($items, $currency)[$type];
        $summary->task = $this->calcItemsTotal($this->filterItemsByType($items, BudgetItemType::Task), $currency)[$type];
        $summary->material = $this->calcItemsTotal($this->filterItemsByType($items, BudgetItemType::Material), $currency)[$type];
        $summary->infra = $this->calcItemsTotal($this->filterItemsByType($items, BudgetItemType::Infrastructure), $currency)[$type];

        return $summary;
    }
}
