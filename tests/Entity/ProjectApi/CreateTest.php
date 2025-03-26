<?php

namespace App\Tests\Entity\ProjectApi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class CreateTest extends ApiTestCase
{
    use ResetDatabase;

    private EntityManagerInterface $entityManager;

    private const USER_EMAIL = 'testuser@example.com';
    private const USER_PASSWORD = 'projectapitestuserpassword';
    private const POST_URL = '/v4/projects';

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    // Auxiliary functions

    private function createTestUser(): User
    {
        $user = new User();
        $user->setHandle('test_user');
        $user->setEmail(self::USER_EMAIL);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        $user->setPassword($passwordHasher->hashPassword($user, self::USER_PASSWORD));

        return $user;
    }

    private function prepareTestUser(): void
    {
        $this->entityManager->persist($this->createTestUser());
        $this->entityManager->flush();
    }

    private function getValidToken(Client $client): string
    {
        $this->prepareTestUser();

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
        
        return json_decode($client->getResponse()->getContent(), true)['token'];
    }

    private function getHeaders(Client $client): array
    {
        return [
            'Authorization' => "Bearer " . $this->getValidToken($client),
            'Content-Type' => 'application/json'
        ];
    }

    // TESTS

    public function testPostWithValidToken(): void
    {
        $expectedData = [
            'title' => 'New Education Project',
            'subtitle' => 'Education for the Future',
            'category' => 'education',
            'territory' => ['country' => 'ES'],
            'description' => 'Detailed project description',
            'deadline' => 'minimum',
            'video' => 'https://www.youtube.com/watch?v=bnrVQHEXmOk',
        ];

        $client = static::createClient();
        $client->request('POST', self::POST_URL, [
            'headers' => $this->getHeaders($client),
            'json' => $expectedData,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);

        $expectedSubset = $expectedData;
        unset($expectedSubset['video']);

        $this->assertArraySubset($expectedSubset, $responseData);
    }

    public function testPostWithoutMandatoryField(): void
    {
        // Expected data without the 'title' field
        $requestData = [
            'subtitle' => 'Education for the Future',
            'category' => 'education',
            'territory' => ['country' => 'ES'],
            'description' => 'Detailed project description',
            'deadline' => 'minimum',
            'video' => 'https://www.youtube.com/watch?v=bnrVQHEXmOk',
        ];

        $client = static::createClient();
        $client->request('POST', self::POST_URL, [
            'headers' => $this->getHeaders($client),
            'json' => $requestData,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPostWithInvalidInput(): void
    {
        $requestData = [
            'title' => 'New Education Project',
            'subtitle' => 'Education for the Future',
            'category' => 'nonexistent-category', // invalid category
            'territory' => ['country' => 'ES'],
            'description' => 'Detailed project description',
            'deadline' => 'minimum',
            'video' => 'https://www.youtube.com/watch?v=bnrVQHEXmOk',
        ];

        $client = static::createClient();
        $client->request('POST', self::POST_URL, [
            'headers' => $this->getHeaders($client),
            'json' => $requestData,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPostUnauthorized()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            self::POST_URL,
            [
                'json' => [
                    'title' => 'ProjectApiTest Project',
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testPostWithInvalidToken()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/v4/projects',
            [
                'headers' => [
                    'Authorization' => "Bearer invalid_token",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'title' => 'ProjectApiTest Project',
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
