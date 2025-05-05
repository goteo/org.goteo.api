<?php

namespace App\ApiResource\Matchfunding;

use ApiPlatform\Metadata as API;
use App\Matchfunding\Rule\RuleInterface;
use App\State\Matchfunding\RuleStateProvider;

/**
 * A MatchRule is a predefined code implementation for validating
 * that a match making condition is met when there is a Charge to be matched.
 */
#[API\ApiResource(
    shortName: 'MatchRule',
    provider: RuleStateProvider::class
)]
#[API\GetCollection()]
#[API\Get()]
class RuleApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public string $name;

    /**
     * A plain-text description about what the rules validates for.
     */
    #[API\ApiProperty(writable: false)]
    public string $description;

    public static function from(RuleInterface $interface): RuleApiResource
    {
        $resource = new RuleApiResource();
        $resource->name = \array_slice(\explode('\\', $interface::class), -1, 1)[0];
        $resource->description = $interface::getDescription();

        return $resource;
    }
}
