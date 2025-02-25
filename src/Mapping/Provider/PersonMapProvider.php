<?php

namespace App\Mapping\Provider;

use App\ApiResource\User\PersonApiResource;
use App\Repository\User\PersonRepository;
use AutoMapper\Provider\ProviderInterface;

class PersonMapProvider implements ProviderInterface
{
    public function __construct(
        private PersonRepository $personRepository,
    ) {}

    /**
     * @param PersonApiResource $source
     */
    public function provide(string $targetType, mixed $source, array $context): object|array|null
    {
        return $this->personRepository->find($source->user->id);
    }
}
