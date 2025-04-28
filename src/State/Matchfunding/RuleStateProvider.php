<?php

namespace App\State\Matchfunding;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Matchfunding\RuleApiResource;
use App\Matchfunding\Rule\RuleInterface;
use App\Matchfunding\Rule\RuleLocator;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

class RuleStateProvider implements ProviderInterface
{
    public function __construct(
        private RuleLocator $ruleLocator,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (\array_key_exists('name', $uriVariables)) {
            try {
                $rule = $this->ruleLocator->getOne(\sprintf(
                    'App\\Matchfunding\\Rule\\%s',
                    $uriVariables['name']
                ));

                return $this->getApiResource($rule);
            } catch (ClassNotFoundException $e) {
                return null;
            }
        }

        $strategies = $this->ruleLocator->getAll();

        $resources = [];
        foreach ($strategies as $rule) {
            $resources[] = $this->getApiResource($rule);
        }

        return $resources;
    }

    private function getApiResource(RuleInterface $rule): RuleApiResource
    {
        $resource = new RuleApiResource();
        $resource->name = self::getName($rule::class);
        $resource->description = $rule::getDescription();

        return $resource;
    }

    private static function getName(string $class): string
    {
        return \array_slice(\explode('\\', $class), -1, 1)[0];
    }
}
