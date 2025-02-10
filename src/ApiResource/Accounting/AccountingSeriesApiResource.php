<?php

namespace App\ApiResource\Accounting;

use ApiPlatform\Metadata as API;
use App\Dto\AccountingSeriesDto;
use App\State\Accounting\AccountingSeriesStateProcessor;

/**
 * An AccountingSeries displays plottable, aggregated balance data over a time range for a given Accounting.\
 * \
 * AccountingSeries are calculated in real-time for each request from the available data for the Accountings,
 * each data point is a Money object representing the aggregated balance of the Accounting at that point in the series.\
 * \
 * Data points are relative to the series unit of time,
 * each point represents a lapse of `unit` over the `startDate`.
 */
#[API\ApiResource(shortName: 'AccountingSeries')]
#[API\Post(
    input: AccountingSeriesDto::class,
    processor: AccountingSeriesStateProcessor::class
)]
class AccountingSeriesApiResource
{
    /**
     * The source Accounting for the data series.
     */
    public AccountingApiResource $accounting;

    /**
     * The start date for the data series.
     */
    public \DateTimeInterface $start;

    /**
     * The time unit for each data point in the series.
     */
    public string $interval;

    /**
     * The end date for the data series. Inclusive.
     */
    public \DateTimeInterface $end;

    /**
     * The number of data points in the series.
     */
    public int $length;

    /**
     * @var array<int, \App\Entity\Money>
     */
    public array $data;
}
