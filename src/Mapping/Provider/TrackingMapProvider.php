<?php

namespace App\Mapping\Provider;

use App\Gateway\Tracking;
use App\Repository\Gateway\TrackingRepository;
use AutoMapper\Provider\ProviderInterface;

class TrackingMapProvider implements ProviderInterface
{
    public function __construct(
        private TrackingRepository $trackingRepository,
    ) {}

    /**
     * @param Tracking $source
     */
    public function provide(string $targetType, mixed $source, array $context): object|array|null
    {
        return $this->trackingRepository->findOneBy([
            'title' => $source->title,
            'value' => $source->value,
        ]);
    }
}
