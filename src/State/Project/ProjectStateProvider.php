<?php

namespace App\State\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Project\ProjectApiResource;
use App\Doctrine\LocalizedExtensionTrait;
use App\Mapping\AutoMapper;
use App\Repository\Project\ProjectRepository;

class ProjectStateProvider implements ProviderInterface
{
    use LocalizedExtensionTrait;

    public function __construct(
        private ProjectRepository $projectRepository,
        private AutoMapper $autoMapper,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $idOrSlug = $uriVariables['idOrSlug'];

        $queryBuilder = $this->projectRepository->createQueryBuilder('p');
        $queryBuilder->where(\is_numeric($idOrSlug) ? 'p.id = :value' : 'p.slug = :value');
        $queryBuilder->setParameter('value', $idOrSlug);

        $query = $this->addLocalizationHints($queryBuilder, $this->getAcceptedLanguages($context));

        $project = $query->getOneOrNullResult();

        if ($project === null) {
            return null;
        }

        return $this->autoMapper->map($project, ProjectApiResource::class);
    }
}
