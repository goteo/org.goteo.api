<?php

namespace App\Security\Voter;

use App\ApiResource\User\PersonApiResource;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class PersonVoter extends Voter
{
    public const EDIT = 'PERSON_EDIT';
    public const VIEW = 'PERSON_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof PersonApiResource;
    }

    /**
     * @param PersonApiResource $subject
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
