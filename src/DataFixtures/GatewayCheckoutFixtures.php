<?php

namespace App\DataFixtures;

use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Factory\Project\GatewayCheckoutFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use function Zenstruck\Foundry\faker;

class GatewayCheckoutFixtures extends Fixture
{
    public function load(ObjectManager $manager): void {}

    public function loadFactories(): void
    {
        $origin = $this->getReference(UserFixtures::USER_EMAIL, User::class)->getAccounting();
        $target = $this->getReference(ProjectFixtures::PROJECT_REFERENCE, Project::class)
            ->getAccounting();

        GatewayCheckoutFactory::createOne([
            'origin' => $origin,
            'charges' => [
                'type' => 'single',
                'title' => faker(),
                'target' => $target,
                'money' => [
                    'amount' => faker()->randomNumber(4),
                    'currency' => 'EUR',
                ],
            ],
        ]);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProjectFixtures::class,
        ];
    }
}
