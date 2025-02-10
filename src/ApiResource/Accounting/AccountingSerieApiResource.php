<?php

namespace App\ApiResource\Accounting;

use ApiPlatform\Metadata as API;
use App\Dto\AccountingSerieDto;
use App\State\Accounting\AccountingSerieStateProcessor;

/**
 * An AccountingSerie displays plottable, aggregated balance data over a time range for a given Accounting.\
 * \
 * AccountingSeries are calculated in real-time for each request from the available data for the Accountings,
 * each data point is a Money object representing the aggregated balance of the Accounting at that point in the serie.\
 * \
 * Data points are relative to the series and do not relate to any specific unit of time.\
 * You might get data points for specific days when you make the `maxLength` match the number of days in your date range,
 * but the behaviour is not guaranteed and data points might be less than your desired output if there is not enough data.
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
