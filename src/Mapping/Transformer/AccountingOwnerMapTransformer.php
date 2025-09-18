<?php

namespace App\Mapping\Transformer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\ApiResource\Matchfunding\MatchCallApiResource;
use App\ApiResource\Project\ProjectApiResource;
use App\ApiResource\TipjarApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Matchfunding\MatchCall;
use App\Entity\Project\Project;
use App\Entity\Tipjar;
use App\Entity\User\User;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\Proxy;

class AccountingOwnerMapTransformer implements PropertyTransformerInterface
{
    public function __construct(
        private IriConverterInterface $iriConverter,
    ) {}

    public function transform(mixed $value, object|array $source, array $context): mixed
    {
        $owner = self::getAsResource($value);

        return $this->iriConverter->getIriFromResource($owner);
    }

    private static function getAsResource(object $entity): object
    {
        $entityClass = $entity::class;

        if ($entity instanceof Proxy) {
            $entityClass = ClassUtils::getRealClass($entityClass);
        }

        switch ($entityClass) {
            case User::class:
                return self::buildFromEntityWithId($entity, UserApiResource::class);
            case Project::class:
                return self::buildFromEntityWithId($entity, ProjectApiResource::class);
            case Tipjar::class:
                return self::buildFromEntityWithId($entity, TipjarApiResource::class);
            case MatchCall::class:
                return self::buildFromEntityWithId($entity, MatchCallApiResource::class);
            default:
                throw new \Exception(\sprintf(
                    'Object of class %s could not be mapped to an Accounting owner resource',
                    $entityClass
                ));
        }
    }

    private static function buildFromEntityWithId(object $entity, string $resourceClass): object
    {
        $resource = new $resourceClass();
        $resource->id = $entity->getId();

        return $resource;
    }
}
