<?php

namespace App\Tests\Entity\ProjectApi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Project\ProjectTerritory;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
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

    private const PAGE_SIZE = 30;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    // Auxiliary functions

    private function createTestUser()
    {
        return UserFactory::createOne([
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);
    }

    private function createTestProjectOptimized(int $count)
    {
        $owner = $this->createTestUser();
        $territory = new ProjectTerritory('ES');

        // It passes an attributes already created because memory management is critical here
        ProjectFactory::createMany($count, [
            'owner' => $owner,
            'territory' => $territory,
        ]);
    }

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
        $owner = $this->createTestUser();
        $numberOfProjects = 2;
        ProjectFactory::createMany($numberOfProjects, ['owner' => $owner]);

        $client = static::createClient();
        $client->request(self::METHOD, self::BASE_URI, $this->getHeaders($client));

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount($numberOfProjects, $responseData['member']);
    }

    private function getMinNumInPage($page = 1, $pageSize = self::PAGE_SIZE)
    {
        return $pageSize * ($page - 1);
    }

    public function testGetAllOnPage(): void
    {
        $page = 2;
        $numberOfProjectsInPage = 1;
        $itemsPerPage = 2;
        $minCountInPage = $this->getMinNumInPage($page, $itemsPerPage);
        $totalNumberOfProjects = $minCountInPage + $numberOfProjectsInPage;

        $this->createTestProjectOptimized($totalNumberOfProjects);

        $client = static::createClient();
        $client->request(
            self::METHOD,
            self::BASE_URI."?page=$page&itemsPerPage=$itemsPerPage",
            $this->getHeaders($client)
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($totalNumberOfProjects, $responseData['totalItems']);
        $this->assertCount(max($numberOfProjectsInPage, 0), $responseData['member']);

        $this->entityManager->clear();
    }

    public function testGetAllDefaultsFirstPage(): void
    {
        $itemsPerPage = 2;
        $totalNumberOfProjects = $itemsPerPage + 1;

        $this->createTestProjectOptimized($totalNumberOfProjects);

        $client = static::createClient();
        $client->request(
            self::METHOD,
            self::BASE_URI."?itemsPerPage=$itemsPerPage",
            $this->getHeaders($client)
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($totalNumberOfProjects, $responseData['totalItems']);
        $this->assertCount($itemsPerPage, $responseData['member']);

        $this->entityManager->clear();
    }

    public function testGetAllUnauthorized(): void
    {
        static::createClient()->request(self::METHOD, self::BASE_URI);

        $this->assertResponseIsSuccessful();
    }

    public function testGetAllWithInvalidToken(): void
    {
        static::createClient()->request(
            self::METHOD,
            self::BASE_URI,
            ['headers' => ['Authorization' => 'Bearer 123']]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
