<?php

namespace App\State\Accounting;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Accounting\AccountingSerieApiResource;
use App\Dto\AccountingSerieDto;
use App\Entity\Accounting\Accounting;
use App\Mapping\AutoMapper;
use App\Service\AccountingService;

class AccountingSerieStateProcessor implements ProcessorInterface
{
    public function __construct(
        private AutoMapper $autoMapper,
        private AccountingService $accountingService,
    ) {}

    /**
     * @param AccountingSerieDto $data
     *
     * @return AccountingSerieApiResource
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Accounting */
        $accounting = $this->autoMapper->map($data->accounting, Accounting::class);

        $balanceData = $this->accountingService->calcBalanceSerie(
            $accounting,
            $data->dateStart,
            $data->dateEnd,
            $data->maxLength
        );

        /** @var AccountingSerieApiResource */
        $serie = $this->autoMapper->map($data, AccountingSerieApiResource::class);

        $serie->data = $balanceData;
        $serie->length = \count($balanceData);

        return $serie;
    }
}
