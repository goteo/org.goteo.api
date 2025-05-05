<?php

namespace App\Mapping\Transformer;

use App\ApiResource\Matchfunding\RuleApiResource;
use App\Matchfunding\Rule\RuleLocator;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerInterface;

class MatchRulesMapTransformer implements PropertyTransformerInterface
{
    public function __construct(
        private RuleLocator $ruleLocator,
    ) {}

    public function transform(mixed $value, object|array $source, array $context): mixed
    {
        $rules = $this->ruleLocator->getFrom($source);

        return \array_values(\array_map(fn($r) => RuleApiResource::from($r), $rules));
    }
}
