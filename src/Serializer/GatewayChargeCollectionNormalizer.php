<?php

namespace App\Serializer;

use ApiPlatform\State\Pagination\TraversablePaginator;
use App\ApiResource\Gateway\ChargeApiResource;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GatewayChargeCollectionNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TraversablePaginator
            && $data->getIterator()[0] instanceof ChargeApiResource;
    }

    public function normalize($data, ?string $format = null, array $context = []): array
    {
        $normalized = $this->normalizer->normalize($data, $format, $context);

        if (is_iterable($data)) {
            $normalized = [];
            foreach ($data as $item) {
                $normalized[] = $this->normalizer->normalize($item, $format, $context);
            }
        } else {
            $normalized = $this->normalizer->normalize($data, $format, $context);
        }

        // TODO: Replace with actual logic to calculate total contributions and tips
        $totalContributions = 300;
        $totalTips = 400;

        $normalized['totalContributions'] = $totalContributions;
        $normalized['totalTips'] = $totalTips;

        return $normalized;
    }
}
