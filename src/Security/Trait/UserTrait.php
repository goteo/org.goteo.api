<?php

namespace App\Security\Trait;

use App\Entity\User\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Service\Attribute\Required;

trait UserTrait
{
    private Security $security;

    #[Required]
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    /**
     * Verify and get the authenticated user.
     *
     * @return User the authenticated user
     *
     * @throws AuthenticationException if there is no authenticated user
     */
    protected function getAuthenticatedUser(): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User || $user == null) {
            throw new AuthenticationException();
        }

        return $user;
    }

    /**
     * Verify if the user has the administrator role.
     *
     * @param User $user the authenticated user
     *
     * @return bool true if the user is administrator, false otherwise
     */
    protected function isAdmin(User $user): bool
    {
        return $user->hasRoles(['ROLE_ADMIN']);
    }
}
