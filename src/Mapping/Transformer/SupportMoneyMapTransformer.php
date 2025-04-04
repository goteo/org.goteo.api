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
        $charges = $source->getCharges();
        foreach ($charges as $charge) {
            $money = $this->moneyService->add(
                $charge->getMoney(),
                $money ?? new Money(0, $charge->getMoney()->currency)
            );
        }

        return $money;
    }
}
