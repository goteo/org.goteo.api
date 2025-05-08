<?php

namespace App\DataFixtures;

use App\Entity\User\User;
use App\Factory\User\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Test\Factories;

class UserFixtures extends Fixture
{
    use Factories;

    public const USER_EMAIL = 'testuser@example.com';
    public const OTHER_USER_EMAIL = 'othertestuser@example.com';
    public const USER_PASSWORD = 'projectapitestuserpassword';

    // ObjectManager is not used directly because Foundry handles persistence.
    public function load(ObjectManager $manager): void
    {
        $this->loadFactories();
    }

    protected function createTestUser(
        string $handle = 'test_user',
        string $email = self::USER_EMAIL,
    ): User {
        return UserFactory::createOne([
            'handle' => $handle,
            'email' => $email,
            'password' => self::USER_PASSWORD,
        ]);
    }

    public function loadFactories(): void
    {
        $user = $this->createTestUser();

        $this->addReference(self::USER_EMAIL, $user);

        $otherUser = $this->createTestUser('other', self::OTHER_USER_EMAIL);

        $this->addReference(self::OTHER_USER_EMAIL, $otherUser);
    }
}
