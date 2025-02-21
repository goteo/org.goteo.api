<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NOT FINANCIALLY SAFE.\
 * Use `Brick\Money` to perform monetary calculations.
 *
 * @see \Brick\Money
 */
#[ORM\Embeddable]
class Money
{
    /**
     * An amount of currency.\
     * Expressed as the minor unit, e.g: cents, pennies, etc.
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    public readonly int $amount;

    /**
     * 3-letter ISO 4217 currency code.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    public readonly string $currency;

    public function __construct(
        int $amount,
        string $currency,
    ) {
        $this->amount = $amount;
        $this->currency = $currency;
    }
}
