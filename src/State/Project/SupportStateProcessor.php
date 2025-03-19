<?php

namespace App\State\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Project\SupportApiResource;
use App\Entity\Project\Support;
use App\Mapping\AutoMapper;
use App\Service\Auth\AuthService;
use App\State\EntityStateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SupportStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityStateProcessor $entityStateProcessor,
        private AuthService $authService,
        private AutoMapper $autoMapper,
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // Verify if the user is authenticated
        $user = $this->authService->getUser();
        if (!$user) {
            throw new AuthenticationException();
        }

        // Verify if we are trying to update an existing support
        if (isset($uriVariables['id'])) {
            $support = $this->entityManager->getRepository(Support::class)
                ->find($uriVariables['id']);
            if (!$support) {
                throw new \Exception('Support not found');
            }
        } else {
            $support = new Support();
        }

        $this->autoMapper->map($data, $support);

        $support = $this->entityStateProcessor->process($support, $operation, $uriVariables, $context);

        if ($support === null) {
            return null;
        }

        return $this->autoMapper->map($support, SupportApiResource::class);
    }
}
