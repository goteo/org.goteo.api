<?php

namespace App\Mapping\Provider;

use App\ApiResource\User\OrganizationApiResource;
use App\Repository\User\OrganizationRepository;
use AutoMapper\Provider\ProviderInterface;

class OrganizationMapProvider implements ProviderInterface
{
    public function __construct(
        private OrganizationRepository $orgRepository,
    ) {}

    /**
     * @param OrganizationApiResource $source
     */
    public function provide(string $targetType, mixed $source, array $context): object|array|null
    {
        return $this->orgRepository->find($source->user->id);
    }
}
