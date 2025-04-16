<?php

namespace App\Security\Voter;

use App\ApiResource\Gateway\CheckoutApiResource;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CheckoutVoter extends Voter
{
    use UserOwnedVoterTrait;

    public const EDIT = 'CHECKOUT_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT])
            && $subject instanceof CheckoutApiResource;
    }

    /**
     * @param CheckoutApiResource $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $user->hasRoles(['ROLE_ADMIN'])
                    || $this->isOwnerOf($subject->origin, $user);
        }

        return false;
    }
}
