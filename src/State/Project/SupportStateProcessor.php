<?php

namespace App\State\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\Auth\AuthService;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SupportStateProcessor implements ProcessorInterface
{
    public function __construct(private AuthService $authService) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $owner = $this->authService->getUser();

        if (!$owner) {
            throw new AuthenticationException();
        }

        return $data;
    }
}
