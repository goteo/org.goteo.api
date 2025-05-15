<?php

namespace App\DependencyInjection\Compiler;

use ApiPlatform\Metadata\ApiResource;
use App\Service\VersionedResourceService;
use Gedmo\Mapping\Annotation\Loggable;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class VersionedResourcePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $allClasses = \get_declared_classes();
        $resourceClasses = \array_filter($allClasses, function (string $className) {
            return \str_starts_with($className, 'App\\ApiResource\\');
        });

        $versionedResources = [];
        foreach ($resourceClasses as $resourceClass) {
            $resourceReflection = new \ReflectionClass($resourceClass);
            $resourceAttributes = $resourceReflection->getAttributes(ApiResource::class);

            if (empty($resourceAttributes)) {
                continue;
            }

            $resourceAttribute = $resourceAttributes[0];
            $resourceAttributeArgs = $resourceAttribute->getArguments();

            if (!\array_key_exists('stateOptions', $resourceAttributeArgs)) {
                continue;
            }

            if (!\array_key_exists('shortName', $resourceAttributeArgs)) {
                continue;
            }

            /** @var string */
            $resourceEntityClass = $resourceAttributeArgs['stateOptions']->getEntityClass();

            $entityReflection = new \ReflectionClass($resourceEntityClass);
            $entityLoggable = $entityReflection->getAttributes(Loggable::class);

            if (empty($entityLoggable)) {
                continue;
            }

            $versionedResources = [
                ...$versionedResources,
                $resourceAttributeArgs['shortName'] => $resourceEntityClass,
            ];
        }

        /** @var VersionedResourceService */
        $versionedResourceService = $container->get(VersionedResourceService::class);
        $versionedResourceService->compileNames($versionedResources);
    }
}
