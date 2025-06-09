<?php

namespace App\Matchfunding\Formula;

use App\Matchfunding\Exception\FormulaDuplicatedException;
use App\Matchfunding\Exception\FormulaNotFoundException;

class FormulaLocator
{
    /** @var array<string, FormulaInterface> */
    private array $formulasByName = [];

    public function __construct(
        iterable $formulas,
    ) {
        foreach ($formulas as $formula) {
            $formulaName = $formula::getName();

            if (\array_key_exists($formulaName, $this->formulasByName)) {
                throw new FormulaDuplicatedException(
                    $formulaName,
                    $formula::class,
                    $this->formulasByName[$formulaName]::class
                );
            }

            $this->formulasByName[$formulaName] = $formula;
        }
    }

    /**
     * @return array<string, FormulaInterface>
     */
    public function getAll(): array
    {
        return $this->formulasByName;
    }

    public function get(string $formulaName): ?FormulaInterface
    {
        if (!array_key_exists($formulaName, $this->formulasByName)) {
            throw new FormulaNotFoundException($formulaName);
        }

        return $this->formulasByName[$formulaName];
    }
}
