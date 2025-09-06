<?php

namespace App\Entity;

use App\Money\MoneyInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * NOT FINANCIALLY SAFE.\
 * Use `Brick\Money` to perform monetary calculations.
 *
 * @see \Brick\Money
 */
#[ORM\Embeddable]
class EmbeddableMoney implements MoneyInterface
{
    /**
     * An amount of currency.\
     * Expressed as the minor unit, e.g: cents, pennies, etc.
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    public readonly ?int $amount;

    /**
     * 3-letter ISO 4217 currency code.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    public readonly ?string $currency;

    public function __construct(
        ?int $amount = null,
        ?string $currency = null,
    ) {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function of(MoneyInterface $moneyInterface): self
    {
        return new self(
            $moneyInterface->getAmount(),
            $moneyInterface->getCurrency()
        );
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
