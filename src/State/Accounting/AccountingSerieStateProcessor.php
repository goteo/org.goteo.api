<?php

namespace App\State\Accounting;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\AccountingSerieDto;

class AccountingSerieStateProcessor implements ProcessorInterface
{
    /**
     * @param AccountingSerieDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        dd($data);
    }
}
