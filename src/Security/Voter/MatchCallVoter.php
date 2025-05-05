<?php

namespace App\Security\Voter;

use App\ApiResource\Matchfunding\MatchCallApiResource;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class MatchCallVoter extends Voter
{
    public const EDIT = 'MATCHCALL_EDIT';
    public const VIEW = 'MATCHCALL_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof MatchCallApiResource;
    }

    /**
     * @param MatchCallApiResource $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::VIEW:
                return true;
        }

        return false;
    }

    private function canEdit(MatchCallApiResource $call, ?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasRoles(['ROLE_ADMIN'])) {
            return true;
        }

        if (\in_array($user->getId(), \array_map(fn($m) => $m->id, $call->managers))) {
            return true;
        }

        return false;
    }
}
