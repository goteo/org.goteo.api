<?php

namespace App\Security\Voter;

use App\ApiResource\Accounting\AccountingApiResource;
use App\ApiResource\Project\ProjectApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccountingVoter extends Voter
{
    use UserOwnedVoterTrait;

    public const EDIT = 'ACCOUNTING_EDIT';
    public const VIEW = 'ACCOUNTING_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof AccountingApiResource;
    }

    /**
     * @param AccountingApiResource $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $owner = $subject->getOwner();

        switch ($owner::class) {
            case ProjectApiResource::class:
                return $this->voteOnProject($attribute, $owner, $user);
            case UserApiResource::class:
                return $this->voteOnUser($attribute, $owner, $user);
        }

        return false;
    }

    private function voteOnProject(string $attribute, ProjectApiResource $project, ?User $user): bool
    {
        switch ($attribute) {
            case self::EDIT:
                return $user->hasRoles(['ROLE_ADMIN'])
                    || $this->isOwnerOf($project, $user);
            case self::VIEW:
                return true;
        }

        return false;
    }

    private function voteOnUser(string $attribute, UserApiResource $owner, ?User $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
            case self::VIEW:
                return $user->hasRoles(['ROLE_ADMIN'])
                    || $this->isOwnerOf($owner, $user);
        }

        return false;
    }
}
