<?php

namespace App\Tests\Fixtures;

use App\Entity\User\User;
use App\Factory\User\UserFactory;
use Zenstruck\Foundry\Test\Factories;

final class TestUser
{
    use Factories;

    private static ?User $user = null;

    public static function get(): User
    {
        if (!self::$user) {
            self::$user = UserFactory::new()
                ->test()
                ->create();
        }

        return self::$user;
    }
}
