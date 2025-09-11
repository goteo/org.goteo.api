<?php

namespace App\Money\Conversion;

use App\Money\Money;
use App\Money\MoneyInterface;
use Brick\Math\RoundingMode;

class Conversion
{
    public const RoundingMode DEFAULT_ROUNDING = RoundingMode::UP;

    private ?string $rounding = null;

    /**
     * @param ?RoundingMode $roundingMode if supplied, then `rounding` will be set from here
     */
    public function __construct(
        private MoneyInterface $from,
        private MoneyInterface $to,
        private float $rate,
        private string $date,
        private string $provider,
        ?string $rounding = null,
        ?RoundingMode $roundingMode = null,
    ) {
        if ($rounding) {
            $this->rounding = $rounding;
        }

        if ($roundingMode) {
            $this->rounding = self::getRoundingName($roundingMode);
        }

        if ($this->rounding === null) {
            throw new \Exception('Conversion rounding cannot be null');
        }
    }

    /**
     * @return Money the original Money that was converted from
     */
    public function getFrom(): Money
    {
        return $this->from;
    }

    /**
     * @return Money the conversion result Money
     */
    public function getTo(): Money
    {
        return $this->to;
    }

    /**
     * @return float the conversion rate given by the exchange rate provider
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @return string the date of the rate as given by the exchange rate provider
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return string the ExchangeInterface implementation name
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @return string the rounding mode used during conversion
     */
    public function getRounding(): string
    {
        return $this->rounding;
    }

    public static function getRoundingName(RoundingMode $rounding): string
    {
        return match ($rounding) {
            RoundingMode::UP => 'up',
            RoundingMode::DOWN => 'down',
            RoundingMode::FLOOR => 'floor',
            RoundingMode::CEILING => 'ceiling',
            RoundingMode::HALF_UP => 'half_up',
            RoundingMode::HALF_DOWN => 'half_down',
            RoundingMode::HALF_FLOOR => 'half_floor',
            RoundingMode::HALF_CEILING => 'half_ceiling',
            RoundingMode::HALF_EVEN => 'half_even',
        };
    }

    public function toArray(): array
    {
        return [
            'from' => [
                'amount' => $this->from->getAmount(),
                'currency' => $this->from->getCurrency(),
            ],
            'to' => [
                'amount' => $this->to->getAmount(),
                'currency' => $this->to->getCurrency(),
            ],
            'rate' => $this->rate,
            'date' => $this->date,
            'provider' => $this->provider,
            'rounding' => $this->rounding,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            from: new Money(
                $data['from']['amount'],
                $data['from']['currency']
            ),
            to: new Money(
                $data['to']['amount'],
                $data['to']['currency']
            ),
            rate: (float) $data['rate'],
            date: $data['date'],
            provider: $data['provider'],
            rounding: $data['rounding'] ?? null,
        );
    }
}
