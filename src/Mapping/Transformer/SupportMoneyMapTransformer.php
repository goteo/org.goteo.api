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
        $money = new Money(0, $source->getProject()->getAccounting()->getCurrency());

        $charges = $source->getCharges();
        foreach ($charges as $charge) {
            $money = $this->moneyService->add($charge->getMoney(), $money);
        }

        return $money;
    }
}
