<?php

namespace App\Entity;

use App\Money\Conversion\Conversion;
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
    private ?int $amount;

    /**
     * 3-letter ISO 4217 currency code.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $currency;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $conversion;

    public function __construct(
        ?int $amount = null,
        ?string $currency = null,
        ?Conversion $conversion = null,
    ) {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->conversion = $conversion?->toArray();
    }

    public static function of(MoneyInterface $moneyInterface): self
    {
        return new self(
            $moneyInterface->getAmount(),
            $moneyInterface->getCurrency(),
            $moneyInterface->getConversion()
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

    public function getConversion(): ?Conversion
    {
        return $this->conversion
            ? Conversion::fromArray($this->conversion)
            : null;
    }
}
