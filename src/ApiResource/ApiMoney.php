<?php

namespace App\ApiResource;

use ApiPlatform\Metadata as API;
use App\Money\Conversion\Conversion;
use App\Money\MoneyInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ApiMoney implements MoneyInterface
{
    /**
     * An amount of currency.\
     * Expressed as the minor unit, e.g: cents, pennies, etc.
     */
    #[Assert\NotBlank()]
    #[Assert\Positive()]
    public int $amount;

    /**
     * 3-letter ISO 4217 currency code.
     */
    #[Assert\NotBlank()]
    #[Assert\Currency()]
    public string $currency;

    /**
     * Conversion metadata.
     */
    #[API\ApiProperty(readable: true, writable: false)]
    public ?array $conversion;

    public function __construct(
        int $amount,
        string $currency,
    ) {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function of(MoneyInterface $moneyInterface): self
    {
        $money = new self(
            $moneyInterface->getAmount(),
            $moneyInterface->getCurrency(),
        );

        $money->conversion = $moneyInterface->getConversion()?->toArray();

        return $money;
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
