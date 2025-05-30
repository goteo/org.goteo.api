<?php

namespace App\Tests\Entity\ProjectApi;

use Symfony\Component\HttpFoundation\Response;

class CreateTest extends BaseTest
{
    // Auxiliary functions

    protected function getMethod(): string
    {
        return 'POST';
    }

    private function assertProjectData(array $expectedData, array $responseData): void
    {
        $this->assertArrayHasKey('id', $responseData);

        $expectedSubset = $expectedData;
        unset($expectedSubset['video']);

        $this->assertArraySubset($expectedSubset, $responseData);

        $this->assertEquals(
            $expectedData['territory']['country'],
            $responseData['territory']['country']
        );

        $this->assertMatchesRegularExpression('/^https?:\/\//', $responseData['video']['src']);
    }

    // TESTS

    // Auxiliary Tests

    private function testPostSetBase(
        array $setData,
        int $expectedCode = Response::HTTP_CREATED,
    ): void {
        $requestData = [
            'title' => 'New Education Project',
            'subtitle' => 'Education for the Future',
            'category' => 'education',
            'territory' => ['country' => 'ES'],
            'description' => 'Detailed project description',
            'deadline' => 'minimum',
            'video' => 'https://www.youtube.com/watch?v=bnrVQHEXmOk',
        ];

        $requestData = array_merge($requestData, $setData);

        $this->createTestUser();
        $this->testRequestHelper($requestData, self::BASE_URI, $expectedCode);
    }

    private function testPostWithInvalidInput(array $invalidData): void
    {
        $expectedCode = Response::HTTP_BAD_REQUEST;
        $this->testPostSetBase($invalidData, $expectedCode);
    }

    private function testPostWithUnprocessableEntity(array $invalidData): void
    {
        $expectedCode = Response::HTTP_UNPROCESSABLE_ENTITY;
        $this->testPostSetBase($invalidData, $expectedCode);
    }

    // Runable Tests

    public function testPostWithValidToken(): void
    {
        $this->createTestUser();

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
        $client->request('POST', self::BASE_URI, $this->getRequestOptions($client, $expectedData));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertProjectData($expectedData, $responseData);
    }

    public function testPostWithoutMandatoryField(): void
    {
        $this->createTestUser();

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
        $client->request('POST', self::BASE_URI, $this->getRequestOptions($client, $requestData));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPostWithInvalidCategory(): void
    {
        $this->testPostWithInvalidInput(['category' => 'nonexistent-category']);
    }

    public function testPostWithInvalidDeadline(): void
    {
        $this->testPostWithInvalidInput(['deadline' => 'extended']);
    }

    public function testPostWithInvalidVideoURL(): void
    {
        $this->testPostWithUnprocessableEntity(['video' => 'invalid-url']);
    }

    public function testPostWithInvalidTerritoryISO(): void
    {
        $this->testPostWithUnprocessableEntity(['territory' => ['country' => 'XX']]);
    }

    public function testPostWithVideoExample(): void
    {
        $url = 'https://example.com/video';
        $expectedCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $this->testPostSetBase(['video' => $url], $expectedCode);
    }

    public function testPostUnauthorized()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            self::BASE_URI,
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
        $this->testInvalidToken($this->getUri());
    }
}
