<?php

namespace App\State\Matchfunding;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Matchfunding\FormulaApiResource;
use App\Matchfunding\Exception\FormulaNotFoundException;
use App\Matchfunding\Formula\FormulaLocator;

class FormulaStateProvider implements ProviderInterface
{
    public function __construct(
        private FormulaLocator $formulaLocator,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (\array_key_exists('name', $uriVariables)) {
            try {
                $formula = $this->formulaLocator->get($uriVariables['name']);

                return FormulaApiResource::from($formula);
            } catch (FormulaNotFoundException $e) {
                return null;
            }
        }

        $strategies = $this->formulaLocator->getAll();

        $resources = [];
        foreach ($strategies as $formula) {
            $resources[] = FormulaApiResource::from($formula);
        }

        return $resources;
    }
}
