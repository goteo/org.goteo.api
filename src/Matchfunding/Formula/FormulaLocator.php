<?php

namespace App\Matchfunding\Formula;

use App\Matchfunding\Formula\Exception\FormulaDuplicatedException;
use App\Matchfunding\Formula\Exception\FormulaNotFoundException;

class FormulaLocator
{
    /** @var array<string, FormulaInterface> */
    private array $strategiesByName = [];

    public function __construct(
        iterable $strategies,
    ) {
        foreach ($strategies as $formula) {
            $formulaName = $formula::getName();

            if (\array_key_exists($formulaName, $this->strategiesByName)) {
                throw new FormulaDuplicatedException(
                    $formulaName,
                    $formula::class,
                    $this->strategiesByName[$formulaName]::class
                );
            }

            $this->strategiesByName[$formulaName] = $formula;
        }
    }

    /**
     * @return array<string, FormulaInterface>
     */
    public function getAll(): array
    {
        return $this->strategiesByName;
    }

    public function get(string $formulaName): ?FormulaInterface
    {
        if (!array_key_exists($formulaName, $this->strategiesByName)) {
            throw new FormulaNotFoundException($formulaName);
        }

        return $this->strategiesByName[$formulaName];
    }
}
