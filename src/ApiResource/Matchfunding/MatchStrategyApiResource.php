<?php

namespace App\ApiResource\Matchfunding;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Money;
use App\Entity\Matchfunding\MatchAgainst;
use App\Entity\Matchfunding\MatchStrategy;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;
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
    public array $rules;

    /**
     * The MatchFormula used to calculate matched funds.
     */
    #[Assert\NotBlank()]
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
}
