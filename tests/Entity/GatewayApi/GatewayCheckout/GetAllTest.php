<?php

namespace App\Tests\Entity\GatewayApi\GatewayCheckout;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\Gateway\CheckoutFactory;
use App\Factory\User\UserFactory;
use App\Gateway\CheckoutStatus;
use App\Tests\Traits\TestHelperTrait;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GetAllTest extends ApiTestCase
{
    use TestHelperTrait;
    use ResetDatabase;
    use Factories;

    public const USER_EMAIL = 'testuser@example.com';
    public const USER_PASSWORD = 'projectapitestuserpassword';

    public const METHOD = 'GET';
    public const BASE_URI = '/v4/gateway_checkouts?page=1';

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

    private function assertChargeIsCorrect($charge)
    {
        $this->assertArrayHasKey('money', $charge);

        $money = $charge['money'];

        $this->assertIsInt($money['amount']);

        $this->assertArrayHasKey('currency', $money);
        $this->assertIsString($money['currency']);
    }

    private function assertArrayKeysExist(array $array, ?array $keys = null)
    {
        $keys ??= [
            'id',
            'gateway',
            'origin',
            'charges',
            'returnUrl',
            'status',
            'links',
            'trackings',
        ];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    private function assertCheckoutIsCorrect($checkout)
    {
        $this->assertNotEmpty($checkout);

        $this->assertArrayKeysExist($checkout);

        $this->assertIsInt($checkout['id']);
        $this->assertIsString($checkout['gateway']);
        $this->assertIsString($checkout['origin']);
        $this->assertIsArray($checkout['charges']);
        $this->assertNotEmpty($checkout['charges']);

        $this->assertChargeIsCorrect($checkout['charges'][0]);

        $this->assertIsString($checkout['returnUrl']);
        $this->assertContains(
            $checkout['status'],
            [CheckoutStatus::Pending->value, CheckoutStatus::Charged->value]
        );

        $this->assertIsArray($checkout['links']);
        $this->assertIsArray($checkout['trackings']);
    }

    // Runable tests

    public function testGetAllSuccessful()
    {
        $client = static::createClient();
        $client->request(
            self::METHOD,
            self::BASE_URI,
            ['headers' => $this->getAuthHeaders($client, self::USER_EMAIL, self::USER_PASSWORD)]
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCheckoutIsCorrect($responseData['member'][0]);
    }

    public function testGetAllWithInvalidToken()
    {
        $this->testInvalidToken(self::BASE_URI, self::METHOD);
    }

    public function testGetAllWithoutAccessToken()
    {
        static::createClient()->request(self::METHOD, self::BASE_URI);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
