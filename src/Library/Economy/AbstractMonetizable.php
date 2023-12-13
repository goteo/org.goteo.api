<?php

namespace App\Library\Economy;

use ApiPlatform\Metadata as API;

abstract class AbstractMonetizable
{
    abstract public function getAmount(): int;

    abstract public function setAmount(int $amount): static;

    abstract public function getCurrency(): string;

    abstract public function setCurrency(string $currency): static;

    public function hasCurrencyOf(AbstractMonetizable $money): bool
    {
        return $this->getCurrency() === $money->getCurrency();
    }

    protected function toBrickMoney(): \Brick\Money\Money
    {
        return \Brick\Money\Money::ofMinor(
            $this->getAmount(),
            $this->getCurrency()
        );
    }

    #[API\ApiProperty(readable: false)]
    public function isZero(): bool
    {
        return $this->toBrickMoney()->isZero();
    }

    public function isLessThan(AbstractMonetizable $money): bool
    {
        return $this->toBrickMoney()->isLessThan($money->toBrickMoney());
    }

    public function isLessThanOrEqualTo(AbstractMonetizable $money): bool
    {
        return $this->toBrickMoney()->isLessThanOrEqualTo($money->toBrickMoney());
    }

    public function isGreaterThan(AbstractMonetizable $money): bool
    {
        return $this->toBrickMoney()->isGreaterThan($money->toBrickMoney());
    }

    public function isGreaterThanOrEqualTo(AbstractMonetizable $money): bool
    {
        return $this->toBrickMoney()->isGreaterThanOrEqualTo($money->toBrickMoney());
    }
}
