<?php

namespace App\State\Matchfunding;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Matchfunding\RuleApiResource;
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
                $rule = $this->ruleLocator->getOne($uriVariables['name']);

                return RuleApiResource::from($rule);
            } catch (ClassNotFoundException $e) {
                return null;
            }
        }

        $strategies = $this->ruleLocator->getAll();

        $resources = [];
        foreach ($strategies as $rule) {
            $resources[] = RuleApiResource::from($rule);
        }

        return $resources;
    }
}
