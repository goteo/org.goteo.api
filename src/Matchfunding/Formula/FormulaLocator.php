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
        foreach ($strategies as $Formula) {
            $FormulaName = $Formula::getName();

            if (\array_key_exists($FormulaName, $this->strategiesByName)) {
                throw new FormulaDuplicatedException(
                    $FormulaName,
                    $Formula::class,
                    $this->strategiesByName[$FormulaName]::class
                );
            }

            $this->strategiesByName[$FormulaName] = $Formula;
        }
    }

    /**
     * @return array<string, FormulaInterface>
     */
    public function getAll(): array
    {
        return $this->strategiesByName;
    }

    public function get(string $FormulaName): ?FormulaInterface
    {
        if (!array_key_exists($FormulaName, $this->strategiesByName)) {
            throw new FormulaNotFoundException($FormulaName);
        }

        return $this->strategiesByName[$FormulaName];
    }
}
