<?php

namespace App\Tests\Entity\ProjectApi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
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

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createTestUser(): User
    {
        $user = new User();
        $user->setHandle('test_user');
        $user->setEmail(self::USER_EMAIL);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        $user->setPassword($passwordHasher->hashPassword($user, self::USER_PASSWORD));

        return $user;
    }

    private function getValidToken()
    {
        $client = static::createClient();

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

    public function testPostWithValidToken(): void
    {
        $client = static::createClient();

        $this->entityManager->persist($this->createTestUser());
        $this->entityManager->flush();

        $client->request('POST', '/v4/projects', [
            'headers' => [
                'Authorization' => "Bearer " . $this->getValidToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'title' => 'ProjectApiTest Project',
                'subtitle' => 'ProjectApiTest Project Subtitle',
                'category' => 'education',
                'territory' => ['country' => 'ES'],
                'description' => 'ProjectApiTest Project Description',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('ProjectApiTest Project', $responseData['title']);
    }

    public function testPostUnauthorized()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/v4/projects',
            [
                'json' => [
                    'title' => 'ProjectApiTest Project',
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
