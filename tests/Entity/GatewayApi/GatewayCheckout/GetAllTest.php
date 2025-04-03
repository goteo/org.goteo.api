<?php

namespace App\Tests\Entity\GatewayApi\GatewayCheckout;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\Gateway\CheckoutFactory;
use App\Factory\User\UserFactory;
use App\Tests\Traits\TestHelperTrait;
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

    public function setUp(): void
    {
        self::bootKernel();

        self::loadCheckouts();
    }

    // Auxiliary functions

    private static function loadCheckouts()
    {
        $user = UserFactory::createOne([
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);

        CheckoutFactory::createOne(['origin' => $user->getAccounting()]);
    }

    // Runable tests

    public function testGetAllSuccessful()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/v4/gateway_checkouts?page=1',
            ['headers' => $this->getAuthHeaders($client, self::USER_EMAIL, self::USER_PASSWORD)]
        );

        $this->assertResponseIsSuccessful();
    }
}
