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

        if ($transactions->isEmpty()) {
            return new Money(0, $this->moneyService::DEFAULT_CURRENCY);
        }

        $money = new Money(0, $transactions->first()->getMoney()->currency);

        foreach ($transactions as $transaction) {
            $money = $this->moneyService->add($transaction->getMoney(), $money);
        }

        return $money;
    }
}
