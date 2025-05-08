<?php

namespace App\DataFixtures;

use App\Entity\User\User;
use App\Factory\Project\ProjectFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProjectFixtures extends Fixture
{
    public const PROJECT_REFERENCE = 'test-project';

    public function load(ObjectManager $manager): void
    {
        $this->loadFactories();
    }

    public function loadFactories(): void
    {
        $owner = $this->getReference(UserFixtures::USER_EMAIL, User::class);
        $project = ProjectFactory::createOne(['owner' => $owner]);

        $this->addReference(self::PROJECT_REFERENCE, $project);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
