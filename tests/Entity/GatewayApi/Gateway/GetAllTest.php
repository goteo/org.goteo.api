<?php

namespace App\Tests\Entity\GatewayApi\Gateway;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Factory\User\UserFactory;
use App\Tests\Traits\TestHelperTrait;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GetAllTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;
    use TestHelperTrait;

    private const USER_EMAIL = 'testuser@example.com';
    private const USER_PASSWORD = 'projectapitestuserpassword';
    private const BASE_URI = '/v4/gateways';
    private const METHOD = 'GET';

    public function setUp(): void
    {
        self::bootKernel();
    }

    // Auxiliary functions

    private function createTestUser(
        string $handle = 'test_user',
        string $email = self::USER_EMAIL,
    ) {
        return UserFactory::createOne([
            'handle' => $handle,
            'email' => $email,
            'password' => self::USER_PASSWORD,
        ]);
    }

    private function getRequestOptions(Client $client)
    {
        $headers = $this->getAuthHeaders(
            $client,
            self::USER_EMAIL,
            self::USER_PASSWORD,
            self::METHOD
        );

        return ['headers' => $headers];
    }

    // Runable Tests

    public function testGetAllSuccessful()
    {
        $this->createTestUser();

        $client = static::createClient();
        $client->request(self::METHOD, self::BASE_URI, $this->getRequestOptions($client));

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $gateways = $responseData['member'];

        $this->assertIsArray($gateways, 'Response must contain an array of gateways');
        $this->assertNotEmpty($gateways, 'There must be at least one gateway in the answer');

        $gateway = $responseData['member'][0];

        $this->assertArrayHasKey('name', $gateway, "Each gateway must have a 'name' field");
        $this->assertArrayHasKey('supports', $gateway, "Each gateway must have a 'support' field");
        $this->assertIsString($gateway['name'], "'name' must be a string");
        $this->assertIsArray($gateway['supports'], "'Supports' must be an array");
        $this->assertEmpty(
            array_diff($gateway['supports'], ['single', 'recurring']),
            "All 'support' values ​​must be 'single' or 'resort'"
        );
    }
}
