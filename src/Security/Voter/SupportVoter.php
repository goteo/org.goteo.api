<?php

namespace App\Security\Voter;

use App\ApiResource\Project\SupportApiResource;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class SupportVoter extends Voter
{
    use UserOwnedVoterTrait;

    public const EDIT = 'SUPPORT_EDIT';
    public const VIEW = 'SUPPORT_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof SupportApiResource;
    }

    /**
     * @param SupportApiResource $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var \App\Entity\User\User */
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($user->hasRoles(['ROLE_ADMIN'])) {
            return true;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->isOwnerOf($subject, $user);
            case self::VIEW:
                return !$subject->anonymous;
        }

        return false;
    }
}
