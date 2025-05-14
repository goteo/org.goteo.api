<?php

namespace App\State;

use ApiPlatform\Metadata as API;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Version;
use App\Service\VersionedResourceService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResourceVersionStateProvider implements ProviderInterface
{
    private LogEntryRepository $versionRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->versionRepository = $this->entityManager->getRepository(LogEntry::class);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        switch ($operation::class) {
            case API\Get::class:
                return $this->getVersion($uriVariables['id']);
            case API\GetCollection::class:
                return $this->getVersions(
                    $context['request']->query->get('resource'),
                    $context['request']->query->get('resourceId')
                );
            default:
                throw new \Exception('Operation not supported for Version resource', 1);
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getVersion(int $id): Version
    {
        $log = $this->versionRepository->find($id);

        if (!$log) {
            throw new NotFoundHttpException('Not Found');
        }

        $entity = $this->entityManager->find($log->getObjectClass(), $log->getObjectId());
        $resourceName = VersionedResourceService::getResourceFromEntity($entity::class);

        return new Version($log, $resourceName);
    }

    /**
     * @return Version[]
     */
    private function getVersions(string $resourceName, int $resourceId): array
    {
        $entityClass = VersionedResourceService::getEntityFromResource($resourceName);

        try {
            $entity = $this->entityManager->find($entityClass, $resourceId);
        } catch (MappingException $e) {
            throw new NotFoundHttpException(sprintf("Resource '%s' does not exist", $resourceName));
        }

        if (!$entity) {
            throw new NotFoundHttpException(sprintf("Resource '%s' with ID '%s' not found", $resourceName, $resourceId));
        }

        $logs = $this->versionRepository->getLogEntries($entity);

        $versions = [];
        foreach ($logs as $log) {
            $versions[] = new Version($log, $resourceName);
        }

        return $versions;
    }
}
