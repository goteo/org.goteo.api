<?php

namespace App\State\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Project\SupportApiResource;
use App\Entity\Project\Support;
use App\Mapping\AutoMapper;
use App\Service\Auth\AuthService;
use App\State\EntityStateProcessor;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SupportStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityStateProcessor $entityStateProcessor,
        private AuthService $authService,
        private AutoMapper $autoMapper
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->authService->getUser();

        if (!$user) {
            throw new AuthenticationException();
        }

        /** @var Support */
        $support = $this->autoMapper->map($data, Support::class);

        $support = $this->entityStateProcessor->process($support, $operation, $uriVariables, $context);

        if ($support === null) {
            return null;
        }

        return $this->autoMapper->map($support, SupportApiResource::class);
    }
}
