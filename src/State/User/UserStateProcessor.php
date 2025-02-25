<?php

namespace App\State\User;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata as API;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User\Organization;
use App\Entity\User\User;
use App\Entity\User\UserType;
use App\Mapping\AutoMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserStateProcessor implements ProcessorInterface
{
    public function __construct(
        private AutoMapper $autoMapper,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
    ) {}

    /**
     * @param UserApiResource $data
     *
     * @return UserApiResource
     */
    public function process(mixed $data, API\Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var User */
        $user = $this->autoMapper->map($data, User::class);

        if ($user->isType(UserType::Organization) && $user->getOrganization() === null) {
            $user->setOrganization(Organization::for($user));
        }

        $user = $this->persistProcessor->process($user, $operation, $uriVariables, $context);

        return $this->autoMapper->map($user, $data);
    }
}
