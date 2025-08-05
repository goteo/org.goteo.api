<?php

namespace App\State\Accounting;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Accounting\AccountingApiResource;
use App\ApiResource\Accounting\AccountingBalance;
use App\Mapping\AutoMapper;
use App\Repository\Accounting\AccountingRepository;
use App\Service\AccountingService;

class AccountingBalanceStateProvider implements ProviderInterface
{
    public function __construct(
        private AutoMapper $autoMapper,
        private AccountingRepository $accountingRepository,
        private AccountingService $accountingService,
    ) {}

    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): object|array|null {
        $accounting = $this->accountingRepository->find($uriVariables['id']);

        if ($accounting === null) {
            return null;
        }

        $balance = new AccountingBalance();

        /** @var AccountingApiResource */
        $resource = $this->autoMapper->map($accounting, AccountingApiResource::class);

        $balance->accounting = $resource;
        $balance->balance = $this->accountingService->calcBalance($accounting);

        return $balance;
    }
}
