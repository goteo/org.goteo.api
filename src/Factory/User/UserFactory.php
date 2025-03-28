<?php

namespace App\Factory\User;

use App\Entity\User\User;
use App\Entity\User\UserType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'active' => self::faker()->boolean(),
            'dateCreated' => self::faker()->dateTime(),
            'dateUpdated' => self::faker()->dateTime(),
            'email' => self::faker()->email(),
            'emailConfirmed' => self::faker()->boolean(),
            'handle' => self::faker()->userName(),
            'migrated' => self::faker()->boolean(),
            'password' => self::faker()->password(),
            'roles' => [],
            'type' => UserType::Individual,
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(function (User $user) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        });
    }
}
