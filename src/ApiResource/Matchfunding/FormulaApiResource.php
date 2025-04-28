<?php

namespace App\ApiResource\Matchfunding;

use ApiPlatform\Metadata as API;
use App\Matchfunding\Formula\FormulaInterface;
use App\State\Matchfunding\FormulaStateProvider;

/**
 * A MatchFormula is a predefined code implementation for matching funds in Transactions under a MatchCall.
 * MatchFormulas can be chosen by the managers in a MatchCall and their behaviour fine-tuned.
 */
#[API\ApiResource(
    shortName: 'MatchFormula',
    provider: FormulaStateProvider::class
)]
#[API\GetCollection()]
#[API\Get()]
class FormulaApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public string $name;

    /**
     * The underlying math expressed as a common-notation formula.
     */
    #[API\ApiProperty(writable: false)]
    public string $expression;

    public static function from(FormulaInterface $interface): FormulaApiResource
    {
        $resource = new FormulaApiResource();
        $resource->name = $interface::getName();
        $resource->expression = $interface::getAsExpression();

        return $resource;
    }
}
