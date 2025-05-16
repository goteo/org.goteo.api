<?php

namespace App\Identifier;

use ApiPlatform\Metadata\UriVariableTransformerInterface;

class IdOrSlugUriVariableTransformer implements UriVariableTransformerInterface
{
    public function transform(mixed $value, array $types, array $context = [])
    {
        if (\is_numeric($value)) {
            return (int) $value;
        }

        return (string) $value;
    }

    public function supportsTransformation(mixed $value, array $types, array $context = []): bool
    {
        return \array_key_exists('idOrSlug', $context['uri_variables_map']);
    }
}
