<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Mapping\AutoMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ApiResourceStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)]
        private ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)]
        private ProviderInterface $itemProvider,
        private AutoMapper $autoMapper,
    ) {}

    private function handleCollection(
        Operation $operation,
        string $resourceClass,
        array $uriVariables = [],
        array $context = [],
    ): TraversablePaginator|array {
        $collection = $this->collectionProvider->provide($operation, $uriVariables, $context);

        $resources = [];
        foreach ($collection as $item) {
            $resources[] = $this->autoMapper->map($item, $resourceClass);
        }

        $isPaginated = filter_var(
            $context['filters']['pagination'] ?? true,
            FILTER_VALIDATE_BOOL
        );

        if (!$isPaginated) {
            return $resources;
        }

        return new TraversablePaginator(
            new \ArrayIterator($resources),
            $collection->getCurrentPage(),
            $collection->getItemsPerPage(),
            $collection->getTotalItems()
        );
    }

    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): object|array|null {
        $resourceClass = $operation->getClass();

        if ($operation instanceof CollectionOperationInterface) {
            return $this->handleCollection($operation, $resourceClass, $uriVariables, $context);
        }

        $item = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$item) {
            return null;
        }

        return $this->autoMapper->map($item, $resourceClass);
    }
}
