<?php

namespace App\Mapping\Transformer;

use App\ApiResource\ApiMoney;
use App\Entity\Project\Support;
use App\Money\Money;
use App\Money\MoneyService;
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
        $money = new Money(0, $source->getProject()->getAccounting()->getCurrency());

        $transactions = $source->getTransactions();
        foreach ($transactions as $transaction) {
            $money = $this->moneyService->add($transaction->getMoney(), $money);
        }

        return ApiMoney::of($money);
    }
}
