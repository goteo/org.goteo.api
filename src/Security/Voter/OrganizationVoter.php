<?php

namespace App\Security\Voter;

use App\ApiResource\User\OrganizationApiResource;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class OrganizationVoter extends Voter
{
    public const EDIT = 'ORGANIZATION_EDIT';
    public const VIEW = 'ORGANIZATION_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof OrganizationApiResource;
    }

    /**
     * @param OrganizationApiResource $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $subject->user->id === $user->getId();
            case self::VIEW:
                return $user->hasRoles(['ROLE_ADMIN'])
                    || $subject->user->id === $user->getId();
        }

        return false;
    }
}
