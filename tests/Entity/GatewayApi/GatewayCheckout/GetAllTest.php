<?php

namespace App\Tests\Entity\GatewayApi\GatewayCheckout;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\Gateway\CheckoutFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserFactory;
use App\Gateway\Wallet\WalletService;
use App\Library\Economy\MoneyService;
use App\Service\Gateway\CheckoutService;
use App\Tests\Traits\TestHelperTrait;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GetAllTest extends ApiTestCase
{
    use TestHelperTrait;
    use ResetDatabase;
    use Factories;

    public const USER_EMAIL = 'testuser@example.com';
    public const OTHER_USER_EMAIL = 'othertestuser@example.com';
    public const USER_PASSWORD = 'projectapitestuserpassword';

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();

        self::ensureKernelShutdown();

        $purger = new ORMPurger();

        self::loadCheckouts();
    }

    // Auxiliary functions

    private static function loadCheckouts()
    {
        $user = UserFactory::createOne([
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);

        // $otherUser = UserFactory::createOne([
        //     'handle' => 'other_user',
        //     'email' => self::OTHER_USER_EMAIL,
        //     'password' => self::USER_PASSWORD
        // ]);

        // $project = ProjectFactory::createOne(['owner' => $otherUser]);

        CheckoutFactory::createOne([
            // 'origin' => $user->getAccounting(),
            // 'target' => $project->getAccounting()
        ]);

        // $container = self::getContainer();

        // $gatewayFactory = new GatewayCheckoutFactory(
        //     $container->get(WalletService::class),
        //     $container->get(MoneyService::class),
        //     $container->get(CheckoutService::class),
        //     $container->get(EntityManagerInterface::class)
        // );
        // $gatewayFactory->createOne([
        //     'origin' => $user->getAccounting(),
        //     'target' => $project->getAccounting()
        // ]);

        //         $container = self::getContainer();

        // GatewayCheckoutFactory::setServices(
        //     $container->get(WalletService::class),
        //     $container->get(MoneyService::class),
        //     $container->get(CheckoutService::class),
        //     $container->get(EntityManagerInterface::class)
        // );

        // GatewayCheckoutFactory::new()->withoutPersisting()->createOne([
        //     'origin' => $user->getAccounting(),
        //     'target' => $project->getAccounting()
        // ]);
    }

    // Runable tests

    // public function testSetup()
    // {
    //     $this->assertArrayHasKey('test', ['test' => 'some value']);
    // }

    public function testGetAllSuccessful()
    {
        $email = self::USER_EMAIL;
        $password = self::USER_PASSWORD;

        $client = static::createClient();
        $client->request(
            'GET',
            '/v4/gateway_checkouts?page=1',
            ['headers' => $this->getAuthHeaders($client, $email, $password)]
        );

        $this->assertResponseIsSuccessful();
    }
}
