<?php

namespace App\Mapping\Transformer;

use App\ApiResource\Matchfunding\FormulaApiResource;
use App\Matchfunding\Formula\FormulaLocator;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerInterface;

class MatchFormulaMapTransformer implements PropertyTransformerInterface
{
    public function __construct(
        private FormulaLocator $formulaLocator,
    ) {}

    public function transform(mixed $value, object|array $source, array $context): mixed
    {
        $interface = $this->formulaLocator->get($value);

        return FormulaApiResource::from($interface);
    }
}
