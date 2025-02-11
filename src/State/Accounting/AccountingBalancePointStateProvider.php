<?php

namespace App\State\Accounting;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameters;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Accounting\Accounting;
use App\Mapping\AutoMapper;
use App\Service\AccountingService;

class AccountingBalancePointStateProvider implements ProviderInterface
{
    public function __construct(
        private IriConverterInterface $iriConverter,
        private AutoMapper $autoMapper,
        private AccountingService $accountingService,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $parameters = $operation->getParameters();

        $accounting = $this->getAccounting($parameters);
        $period = $this->buildPeriod($parameters);

        return $this->accountingService->calcBalancePoints($accounting, $period);
    }

    private function getAccounting(Parameters $parameters): Accounting
    {
        $parameter = $parameters->get('accounting');
        $resource = $this->iriConverter->getResourceFromIri($parameter->getValue());

        /** @var Accounting */
        $accounting = $this->autoMapper->map($resource, Accounting::class);

        return $accounting;
    }

    private function buildPeriod(Parameters $parameters): \DatePeriod
    {
        $start = new \DateTimeImmutable($parameters->get('start')->getValue());

        $interval = $parameters->get('interval')->getValue();
        $interval = new \DateInterval(\sprintf('PT%s', \strtoupper($interval)));

        $end = new \DateTimeImmutable($parameters->get('end')->getValue());

        return new \DatePeriod($start, $interval, $end);
    }
}
