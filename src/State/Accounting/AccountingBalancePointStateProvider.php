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
        $aggregate = $parameters->get('aggregate')->getValue() ?? false;

        return $this->accountingService->calcBalancePoints($accounting, $period, $aggregate);
    }

    private function getAccounting(Parameters $parameters): Accounting
    {
        $parameter = $parameters->get('accounting');
        $resource = $this->iriConverter->getResourceFromIri($parameter->getValue());

        /** @var Accounting */
        $accounting = $this->autoMapper->map($resource, Accounting::class);

        return $accounting;
    }

    private function parseInterval(string $input): \DateInterval
    {
        if (!preg_match('/^(\d+)([hdw])$/i', $input, $matches)) {
            throw new \InvalidArgumentException('Interval format must be like 1h, 2d, or 1w.');
        }

        [$_, $amount, $unit] = $matches;

        return match (strtolower($unit)) {
            'h' => new \DateInterval(sprintf('PT%dH', $amount)),
            'd' => new \DateInterval(sprintf('P%dD', $amount)),
            'w' => new \DateInterval(sprintf('P%dD', $amount * 7)),
            default => throw new \LogicException("Unsupported interval unit: $unit"),
        };
    }

    private function buildPeriod(Parameters $parameters): \DatePeriod
    {
        $start = new \DateTimeImmutable($parameters->get('start')->getValue());
        $rawInterval = $parameters->get('interval')->getValue() ?? '24h';
        $end = new \DateTimeImmutable($parameters->get('end')->getValue() ?? 'now');

        $interval = $this->parseInterval($rawInterval);

        return new \DatePeriod($start, $interval, $end);
    }
}
