<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use App\ApiResource\Accounting\AccountingApiResource;
use Symfony\Component\Validator\Constraints as Assert;

class AccountingSeriesDto
{
    /**
     * The source Accounting for the data series.
     */
    #[Assert\NotBlank()]
    public AccountingApiResource $accounting;

    /**
     * The start date for the data series.
     */
    #[Assert\NotBlank()]
    public \DateTimeInterface $start;

    /**
     * The time unit for each data point in the series,
     * expressed as an integer followed by a time unit initial.
     */
    #[Assert\Choice(['24H'])]
    public string $interval = '24H';

    /**
     * The end date for the data series. Inclusive.
     */
    #[Assert\LessThanOrEqual('now')]
    #[ApiProperty(default: 'now')]
    public ?\DateTimeInterface $end = null;

    public function __construct()
    {
        $this->end ??= new \DateTimeImmutable('now');
    }
}
