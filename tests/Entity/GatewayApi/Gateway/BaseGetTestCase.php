<?php

namespace App\Tests\Entity\GatewayApi\Gateway;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Factory\User\UserFactory;
use App\Tests\Traits\TestHelperTrait;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class BaseGetTestCase extends ApiTestCase
{
    use ResetDatabase;
    use Factories;
    use TestHelperTrait;

    protected const USER_EMAIL = 'testuser@example.com';
    protected const USER_PASSWORD = 'projectapitestuserpassword';
    protected const BASE_URI = '/v4/gateways';
    protected const METHOD = 'GET';

    public function setUp(): void
    {
        self::bootKernel();
    }

    // Auxiliary functions

    protected function createTestUser(
        string $handle = 'test_user',
        string $email = self::USER_EMAIL,
    ) {
        return UserFactory::createOne([
            'handle' => $handle,
            'email' => $email,
            'password' => self::USER_PASSWORD,
        ]);
    }

    protected function getRequestOptions(Client $client)
    {
        $headers = $this->getAuthHeaders(
            $client,
            self::USER_EMAIL,
            self::USER_PASSWORD,
            self::METHOD
        );

        return ['headers' => $headers];
    }

    protected function makeGetRequest(
        string $uri = self::BASE_URI,
        $expectedCode = Response::HTTP_OK,
        bool $createUser = true,
    ): mixed {
        if ($createUser) {
            $this->createTestUser();
        }

        $client = static::createClient();
        $client->request(self::METHOD, $uri, $this->getRequestOptions($client));

        $this->assertResponseStatusCodeSame($expectedCode);

        return $client;
    }

    protected function assertGatewayIsCorrect(array $gateway): void
    {
        $this->assertArrayHasKey('name', $gateway, "Each gateway must have a 'name' field");
        $this->assertArrayHasKey('supports', $gateway, "Each gateway must have a 'support' field");
        $this->assertIsString($gateway['name'], "'name' must be a string");
        $this->assertIsArray($gateway['supports'], "'Supports' must be an array");
        $this->assertEmpty(
            array_diff($gateway['supports'], ['single', 'recurring']),
            "All 'support' values ​must be 'single' or 'resort'"
        );
    }

    protected function assertGatewaysAreCorrects(array $gateways): void
    {
        $this->assertJsonContains(['@type' => 'Collection']);
        $this->assertIsArray($gateways, 'Response must contain an array of gateways');
        $this->assertNotEmpty($gateways, 'There must be at least one gateway in the answer');

        $this->assertGatewayIsCorrect($gateways[0]);
    }

    // Auxiliary Tests

    protected function baseTestGetWithInvalidToken(string $uri = self::BASE_URI)
    {
        $this->testInvalidToken(self::METHOD, $uri);
    }

    protected function baseTestGetWithoutToken($uri = self::BASE_URI)
    {
        static::createClient()->request(self::METHOD, $uri);

        // TODO: Change if a 401 must be returned
        $this->assertResponseIsSuccessful();
    }
}
