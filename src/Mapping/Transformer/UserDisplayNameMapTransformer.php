<?php

namespace App\Mapping\Transformer;

use App\Entity\User\User;
use App\Entity\User\UserType;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerInterface;

class UserDisplayNameMapTransformer implements PropertyTransformerInterface
{
    /**
     * @param User $source
     */
    public function transform(mixed $value, object|array $source, array $context): mixed
    {
        switch ($source->getType()) {
            case UserType::Individual:
                return $this->individualDisplayName($source);
            case UserType::Organization:
                return $this->organizationDisplayName($source);
        }
    }

    private function individualDisplayName(User $user): string
    {
        $person = $user->getPerson();

        $name = \sprintf(
            '%s %s',
            $person->getFirstName(),
            $person->getLastName()
        );

        return \trim($name);
    }

    private function organizationDisplayName(User $user): string
    {
        $org = $user->getOrganization();

        $name = $org->getBusinessName();

        if (empty($name)) {
            $name = $org->getBusinessName();
        }

        return $name ?? '';
    }
}
