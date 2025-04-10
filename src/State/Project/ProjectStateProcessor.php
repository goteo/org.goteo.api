<?php

namespace App\State\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Project\ProjectApiResource;
use App\Dto\ProjectCreationDto;
use App\Dto\ProjectUpdationDto;
use App\Entity\Project\Project;
use App\Mapping\AutoMapper;
use App\Service\Auth\AuthService;
use App\State\EntityStateProcessor;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ProjectStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityStateProcessor $entityStateProcessor,
        private AutoMapper $autoMapper,
        private AuthService $authService,
    ) {}

    /**
     * @param ProjectCreationDto|ProjectUpdationDto $data
     * @param array{id: int}                        $uriVariables
     *
     * @return ProjectApiResource|null
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof ProjectCreationDto) {
            /** @var Project */
            $project = $this->autoMapper->map($data, Project::class);

            $owner = $this->authService->getUser();

            if (!$owner) {
                throw new AuthenticationException();
            }

            $project->setOwner($owner);
        }

        if ($data instanceof ProjectUpdationDto) {
            $data = $this->autoMapper->map($uriVariables, $data);

            /** @var Project */
            $project = $this->autoMapper->map($data, Project::class);
        }

        $project = $this->entityStateProcessor->process($project, $operation, $uriVariables, $context);

        if ($project === null) {
            return null;
        }

        return $this->autoMapper->map($project, ProjectApiResource::class);
    }
}
