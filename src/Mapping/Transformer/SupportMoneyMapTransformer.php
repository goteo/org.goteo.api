<?php

namespace App\Mapping\Transformer;

use App\Entity\Money;
use App\Entity\Project\Support;
use App\Library\Economy\MoneyService;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerInterface;

class SupportMoneyMapTransformer implements PropertyTransformerInterface
{
    public function __construct(
        private MoneyService $moneyService,
    ) {}

    /**
     * @param Support $source
     */
    public function transform(mixed $value, object|array $source, array $context): mixed
    {
        $transactions = $source->getTransactions();
        foreach ($transactions as $transaction) {
            $money = $this->moneyService->add(
                $transaction->getMoney(),
                $money ?? new Money(0, $transaction->getMoney()->currency)
            );
        }

        return $money;
    }
}
