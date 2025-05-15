<?php

namespace App\State\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Project\ProjectApiResource;
use App\Mapping\AutoMapper;
use App\Repository\Project\ProjectRepository;

class ProjectStateProvider implements ProviderInterface
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private AutoMapper $autoMapper,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $project = $this->projectRepository->findOneByIdOrSlug($uriVariables['idOrSlug']);

        if ($project === null) {
            return null;
        }

        return $this->autoMapper->map($project, ProjectApiResource::class);
    }
}
