<?php

namespace App\Tests\Entity\GatewayApi\GatewayCheckout;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\GatewayCheckoutFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

class GetAllTest extends ApiTestCase
{
    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $loader = new Loader();
        $loader->addFixture(new GatewayCheckoutFixtures());

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    public function testSetup()
    {
        $this->assertArrayHasKey('test', ['test' => 'some value']);
    }
}
