<?php

namespace App\Mapping\Provider;

use App\ApiResource\Matchfunding\MatchStrategyApiResource;
use App\Repository\Matchfunding\MatchStrategyRepository;
use AutoMapper\Provider\ProviderInterface;

class MatchStrategyMapProvider implements ProviderInterface
{
    public function __construct(
        private MatchStrategyRepository $matchStrategyRepository,
    ) {}

    /**
     * @param MatchStrategyApiResource $source
     */
    public function provide(string $targetType, mixed $source, array $context): object|array|null
    {
        return $this->matchStrategyRepository->find($source->call->id);
    }
}
