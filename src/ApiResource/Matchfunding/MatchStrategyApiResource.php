<?php

namespace App\ApiResource\Matchfunding;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Money;
use App\Entity\Matchfunding\MatchAgainst;
use App\Entity\Matchfunding\MatchStrategy;
use App\Mapping\Transformer\MatchFormulaMapTransformer;
use App\Mapping\Transformer\MatchRulesMapTransformer;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;
use AutoMapper\Attribute\MapFrom;
use AutoMapper\Attribute\MapTo;
use Symfony\Component\Validator\Constraints as Assert;

#[API\ApiResource(
    shortName: 'MatchStrategy',
    stateOptions: new Options(entityClass: MatchStrategy::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class,
    uriTemplate: '/match_call/{id}/strategy',
    uriVariables: [
        'id' => new API\Link(
            fromClass: MatchCallApiResource::class,
            fromProperty: 'strategy',
            description: 'MatchCall identifier'
        ),
    ]
)]
#[API\Get()]
#[API\Patch()]
class MatchStrategyApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public MatchCallApiResource $call;

    /**
     * The MatchRules used to validate the match making strategy.
     * 
     * @var RuleApiResource[]
     */
    #[MapTo(MatchStrategy::class, property: 'ruleNames', transformer: [self::class, 'rulesToNames'])]
    #[MapFrom(MatchStrategy::class, property: 'ruleNames', transformer: MatchRulesMapTransformer::class)]
    public array $rules;

    /**
     * The MatchFormula used to calculate matched funds.
     */
    #[Assert\NotBlank()]
    #[MapTo(MatchStrategy::class, property: 'formulaName', transformer: 'source.formula.name')]
    #[MapFrom(MatchStrategy::class, property: 'formulaName', transformer: MatchFormulaMapTransformer::class)]
    public FormulaApiResource $formula;

    /**
     * The assigned maximum amount of funding that will be given by the MatchFormula per operation.
     */
    #[Assert\NotBlank()]
    #[Assert\Valid()]
    public Money $limit;

    /**
     * The `x` factor used to calculate the resulting match of funds with the MatchFormula.
     */
    #[Assert\NotBlank()]
    #[Assert\Positive()]
    public float $factor;

    /**
     * The money to be matched by the formula is
     * - `charge` the money in the Charge item
     * - `budget_min` the minimum in the Project's budget
     * - `budget_opt` the optimum in the Project's budget.
     */
    public MatchAgainst $match = MatchAgainst::DEFAULT;

    /**
     * @param array<int, RuleApiResource>
     * 
     * @return array<string>
     */
    public static function rulesToNames(array $values)
    {
        return \array_map(fn($r) => $r->name, $values);
    }
}
