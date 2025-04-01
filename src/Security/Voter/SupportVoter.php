<?php

namespace App\Security\Voter;

use App\ApiResource\Project\SupportApiResource;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

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
        /** @var User */
        $user = $token->getUser();

        if ($user instanceof User && $user->hasRoles(['ROLE_ADMIN'])) {
            return true;
        }

        $isOwner = $this->isOwnerOf($subject, $user);

        return match ($attribute) {
            self::EDIT => $isOwner,
            self::VIEW => $isOwner || !$subject->anonymous,
            default => false,
        };
    }
}
