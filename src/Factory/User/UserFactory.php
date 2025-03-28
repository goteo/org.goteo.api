<?php

namespace App\Factory\User;

use App\Entity\User\User;
use App\Entity\User\UserType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'active' => self::faker()->boolean(),
            'dateCreated' => self::faker()->dateTime(),
            'dateUpdated' => self::faker()->dateTime(),
            'email' => self::faker()->text(255),
            'emailConfirmed' => self::faker()->boolean(),
            'handle' => self::faker()->text(255),
            'migrated' => self::faker()->boolean(),
            'password' => self::faker()->text(),
            'roles' => [],
            'type' => self::faker()->randomElement(UserType::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(User $user): void {})
        ;
    }
}
