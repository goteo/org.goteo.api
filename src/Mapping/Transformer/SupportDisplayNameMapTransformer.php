<?php

namespace App\Mapping\Transformer;

use App\Entity\Matchfunding\MatchCall;
use App\Entity\Project\Support;
use App\Entity\User\User;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerInterface;

class SupportDisplayNameMapTransformer implements PropertyTransformerInterface
{
    public function __construct(
        private UserDisplayNameMapTransformer $userDisplayNameMapTransformer,
    ) {}

    /**
     * @param Support $source
     */
    public function transform(mixed $value, object|array $source, array $context): mixed
    {
        return match ($source->getOrigin()->getOwnerClass()) {
            User::class => $this->userDisplayNameMapTransformer->transform($value, $source->getOrigin()->getUser(), $context),
            MatchCall::class => $source->getOrigin()->getMatchCall()->getTitle(),
        };
    }
}
