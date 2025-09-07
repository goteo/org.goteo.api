<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\LinksHandlerLocatorTrait;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\State\LinksHandlerTrait;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Exception\RuntimeException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use App\Money\Totalization\TotalizedMoney;
use App\Money\Totalization\TotalizerLocator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;

class MoneyTotalStateProvider implements ProviderInterface
{
    use LinksHandlerLocatorTrait;
    use LinksHandlerTrait;

    /**
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(
        private readonly iterable $collectionExtensions,
        private TotalizerLocator $totalizerLocator,
        private ManagerRegistry $managerRegistry,
        private ?ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory = null,
        private ?ContainerInterface $handleLinksLocator = null,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TotalizedMoney
    {
        $entityClass = $operation->getClass();
        if (($options = $operation->getStateOptions()) && $options instanceof Options && $options->getEntityClass()) {
            $entityClass = $options->getEntityClass();
        }

        $totalizer = $this->totalizerLocator->get($entityClass);

        return $totalizer->totalize($this->getItems($entityClass, $operation, $uriVariables, $context));
    }

    private function getItems(
        string $entityClass,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): iterable {
        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass($entityClass);

        $repository = $manager->getRepository($entityClass);
        if (!method_exists($repository, 'createQueryBuilder')) {
            throw new RuntimeException('The repository class must have a "createQueryBuilder" method.');
        }

        $queryBuilder = $repository->createQueryBuilder('o');
        $queryNameGenerator = new QueryNameGenerator();

        $this->handleLinks($queryBuilder, $uriVariables, $queryNameGenerator, $context, $entityClass, $operation);

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $entityClass, $operation, $context);

            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($entityClass, $operation, $context)) {
                foreach ($extension->getResult($queryBuilder, $entityClass, $operation, $context) as $item) {
                    $manager->detach($item);

                    yield $item;
                }

                return;
            }
        }

        foreach ($queryBuilder->getQuery()->toIterable() as $item) {
            $manager->detach($item);

            yield $item;
        }
    }
}
