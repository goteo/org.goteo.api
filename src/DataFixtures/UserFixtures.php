<?php

namespace App\DataFixtures;

use App\Factory\User\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const USER_EMAIL = 'testuser@example.com';
    public const OTHER_USER_EMAIL = 'othertestuser@example.com';
    private const USER_PASSWORD = 'projectapitestuserpassword';

    // ObjectManager is not used directly because Foundry handles persistence.
    public function load(ObjectManager $manager): void
    {
        $user = UserFactory::createOne([
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);

        $this->addReference(self::USER_EMAIL, $user);

        $otherUser = UserFactory::createOne([
            'email' => self::OTHER_USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);

        $this->addReference(self::OTHER_USER_EMAIL, $otherUser);
    }
}
