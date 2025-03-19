<?php

namespace App\State\Project;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Project\SupportApiResource;
use App\Entity\Project\Support;
use App\Mapping\AutoMapper;
use App\Service\Auth\AuthService;

class SupportStateProvider implements ProviderInterface
{
    public function __construct(
        private ItemProvider $itemProvider,
        private CollectionProvider $collectionProvider,
        private AutoMapper $autoMapper,
        private AuthService $authService,
    ) {}

    private function filterOwner(SupportApiResource $support): SupportApiResource
    {
        $user = $this->authService->getUser();

        $canSeeAnonymous = $user
            && ($user->hasRoles(['ROLE_ADMIN']) || $support->owner->id === $user->getId());

        if ($support->anonymous && !$canSeeAnonymous) {
            $support->owner = null;
        }

        return $support;
    }

    private function filterResource(mixed $support): SupportApiResource
    {
        /** @var SupportApiResource $resource */
        $resource = $this->autoMapper->map($support, SupportApiResource::class);

        return $this->filterOwner($resource);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|SupportApiResource|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $supports = $this->collectionProvider->provide($operation, $uriVariables, $context);

            return array_map(fn(Support $support) => $this->filterResource($support), iterator_to_array($supports));
        }

        $support = $this->itemProvider->provide($operation, $uriVariables, $context);

        return $support ? $this->filterResource($support) : null;
    }
}
