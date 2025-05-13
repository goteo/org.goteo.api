<?php

namespace App\State\Gateway;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

class ChargeTotalizerStateProvider implements ProviderInterface
{
    public function __construct() {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|object|null
    {
        $parameters = $operation->getParameters();

        // TODO: Implement the logic to calculate the totalizers for the charge.
        return null;
    }
}
