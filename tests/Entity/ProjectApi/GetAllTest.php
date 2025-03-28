<?php

namespace App\Tests\Entity\ProjectApi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GetAllTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;
    protected EntityManagerInterface $entityManager;

    private const USER_EMAIL = 'testuser@example.com';
    private const USER_PASSWORD = 'projectapitestuserpassword';
    private const BASE_URI = '/v4/projects';
    private const METHOD = 'GET';

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    // Auxiliary functions

    private function getHeaders(Client $client)
    {
        // Responsability 1: Get Token
        $client->request(
            'POST',
            '/v4/user_tokens',
            [
                'json' => [
                    'identifier' => self::USER_EMAIL,
                    'password' => self::USER_PASSWORD,
                ],
            ]
        );

        $token = json_decode($client->getResponse()->getContent(), true)['token'];

        // Responsability 2 : Return headers
        return ['headers' => ['Authorization' => "Bearer $token"]];
    }

    // Runable Tests

    public function testGetAllSuccessful(): void
    {
        $owner = UserFactory::createOne([
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);
        $numberOfProjects = 2;
        ProjectFactory::createMany($numberOfProjects, ['owner' => $owner]);

        $client = static::createClient();
        $client->request(self::METHOD, self::BASE_URI, $this->getHeaders($client));

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount($numberOfProjects, $responseData['member']);
    }
}
