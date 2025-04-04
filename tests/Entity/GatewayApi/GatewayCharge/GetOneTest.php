<?php

namespace App\Tests\Entity\GatewayApi\GatewayCharge;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\Gateway\ChargeFactory;
use App\Factory\Gateway\CheckoutFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserFactory;
use App\Gateway\ChargeType;
use App\Tests\Traits\TestHelperTrait;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GetOneTest extends ApiTestCase
{
    use TestHelperTrait;
    use ResetDatabase;
    use Factories;

    private const USER_EMAIL = 'testuser@example.com';
    private const USER_PASSWORD = 'projectapitestuserpassword';

    private const METHOD = 'GET';
    private const BASE_URI = '/v4/gateway_charges';

    private const PAGE_SIZE = 30;
    private const PAGES_TO_FILL = 1;

    public function setUp(): void
    {
        self::bootKernel();

        self::loadCheckouts();
    }

    private static function loadCheckouts(int $count = self::PAGE_SIZE * self::PAGES_TO_FILL + 1)
    {
        $user = UserFactory::createOne([
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);

        $otherUser = UserFactory::createOne([
            'handle' => 'other_user_test',
            'email' => 'otheruser@test.com',
        ]);

        $project = ProjectFactory::createOne(['owner' => $otherUser]);
        $charge = ChargeFactory::createOne(['target' => $project->getAccounting()]);

        CheckoutFactory::createMany($count, [
            'origin' => $user->getAccounting(),
            'charges' => [$charge],
        ]);
    }

    private function getUri(int|string $id): string
    {
        return self::BASE_URI.'/'.$id;
    }

    private function makeRequest(int|string $id)
    {
        $client = static::createClient();
        $client->request(
            self::METHOD,
            $this->getUri($id),
            ['headers' => $this->getAuthHeaders($client, self::USER_EMAIL, self::USER_PASSWORD)]
        );

        return $client;
    }

    private function assertArrayKeysExist(array $array, ?array $keys = null)
    {
        $keys ??= [
            'id',
            'type',
            'title',
            'description',
            'target',
            'money',
        ];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    private function assertChargeIsCorrect($charge)
    {
        $this->assertArrayKeysExist($charge);

        $this->assertIsInt($charge['id']);

        $this->assertContains(
            $charge['type'],
            [ChargeType::Single->value, ChargeType::Recurring->value],
        );

        $this->assertIsString($charge['title']);
        $this->assertIsString($charge['description']);

        $this->assertIsString($charge['target']);
        $this->assertMatchesRegularExpression(
            '/^\/v4\/accountings\/\d+$/',
            $charge['target'],
            'Target must be a valid accounting URI'
        );

        $money = $charge['money'];

        $this->assertArrayKeysExist($money, ['amount', 'currency']);

        $this->assertIsInt($money['amount']);
        $this->assertIsString($money['currency']);
    }

    // Auxiliary tests

    private function baseTestGetOneWithInvalidId(array $ids)
    {
        foreach ($ids as $id) {
            $this->makeRequest($id);

            // Probably must give a 400
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    // Runable tests

    public function testGetOneSuccessful()
    {
        $client = $this->makeRequest(1);

        $this->assertResponseIsSuccessful();

        $responseData = $this->getResponseData($client);
        $this->assertChargeIsCorrect($responseData);
    }

    public function testGetOneWithNotFoundId()
    {
        $this->makeRequest(99999);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJsonContains([]);
    }

    public function testGetOneWithInvalidId()
    {
        $this->baseTestGetOneWithInvalidId(['""', 'null']);
    }

    public function testGetOneWithInvalidAccessToken()
    {
        $this->testInvalidToken(self::METHOD, $this->getUri(1));
    }

    public function testGetOneWithoutAccessToken()
    {
        static::createClient()->request(self::METHOD, $this->getUri(1));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
