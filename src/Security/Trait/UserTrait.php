<?php

namespace App\Security\Trait;

use App\Entity\User\User;
use Symfony\Bundle\SecurityBundle\Security;

trait UserTrait
{
    /**
     * @var Security
     */
    protected Security $security;

    /**
     * Constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * The authenticated user obtains and verifies if it is an instance of User.
     *
     * @return User|null The authenticated user or Null if it is not a valid user.
     */
    protected function getAuthenticatedUser(): ?User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    /**
     * Verify if the user has the administrator role.
     *
     * @param User $user The authenticated user.
     *
     * @return bool True if the user is administrator, false otherwise.
     */
    protected function isAdmin(User $user): bool
    {
        return $user->hasRoles(['ROLE_ADMIN']);
    }
}
