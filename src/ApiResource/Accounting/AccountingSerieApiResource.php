<?php

namespace App\ApiResource\Accounting;

use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\AccountingApiResource;
use App\Dto\AccountingSerieDto;
use App\State\Accounting\AccountingSerieStateProcessor;

/**
 * An AccountingSerie displays plottable aggregated balance data over a time range for a given Accounting.
 */
#[API\ApiResource(shortName: 'AccountingSerie')]
#[API\Post(
    input: AccountingSerieDto::class,
    processor: AccountingSerieStateProcessor::class
)]
class AccountingSerieApiResource
{
    /**
     * The source Accounting for the data serie. 
     */
    public AccountingApiResource $accounting;

    /**
     * The start date for the data serie.
     */
    public \DateTimeInterface $dateStart;

    /**
     * The end date for the data serie. Inclusive.
     */
    public \DateTimeInterface $dateEnd;

    /**
     * The number of data points in the serie.
     */
    public int $length = 10;

    /**
     * @var array<int, \App\Entity\Money>
     */
    public array $data;
}
