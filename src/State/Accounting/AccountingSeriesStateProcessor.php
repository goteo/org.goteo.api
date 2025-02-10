<?php

namespace App\State\Accounting;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Accounting\AccountingSeriesApiResource;
use App\Dto\AccountingSerieDto;
use App\Entity\Accounting\Accounting;
use App\Mapping\AutoMapper;
use App\Service\AccountingService;

class AccountingSeriesStateProcessor implements ProcessorInterface
{
    public function __construct(
        private AutoMapper $autoMapper,
        private AccountingService $accountingService,
    ) {}

    /**
     * @param AccountingSerieDto $data
     *
     * @return AccountingSeriesApiResource
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Accounting */
        $accounting = $this->autoMapper->map($data->accounting, Accounting::class);

        $points = $this->accountingService->calcBalanceSeries(
            $accounting,
            new \DatePeriod(
                $data->start,
                new \DateInterval(\sprintf('PT%s', $data->interval)),
                $data->end,
                \DatePeriod::INCLUDE_END_DATE
            )
        );

        /** @var AccountingSeriesApiResource */
        $series = $this->autoMapper->map($data, AccountingSeriesApiResource::class);

        $series->data = $points;
        $series->length = \count($points);

        return $series;
    }
}
