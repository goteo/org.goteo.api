<?php

namespace App\Tests\Entity\ProjectApi;

use App\Entity\Project\ProjectStatus;
use App\Entity\Territory;
use App\Factory\Project\ProjectFactory;
use Symfony\Component\HttpFoundation\Response;

class GetAllTest extends ProjectTestCase
{
    private const PAGE_SIZE = 30;

    // Auxiliary functions

    protected function getMethod(): string
    {
        return 'GET';
    }

    private function getMinNumInPage($page = 1, $pageSize = self::PAGE_SIZE)
    {
        return $pageSize * ($page - 1);
    }

    private function getString(string|ProjectStatus $data)
    {
        $isEnum = $data instanceof ProjectStatus;

        return $isEnum ? $data->value : (string) $data;
    }

    private function buildQueryParams($param, array $valueNames)
    {
        $query = [];

        foreach ($valueNames as $value) {
            $query["{$param}"][] = $value;
        }

        return ['query' => $query];
    }

    // Auxiliary Tests

    private function testGetAllByParam(
        string $param,
        string|ProjectStatus $searchValue,
        string|ProjectStatus $otherValue,
        $searchCount = 2,
        int $responseCode = Response::HTTP_OK,
    ) {
        $owner = $this->createTestUser();
        $territory = new Territory('ES');
        $baseAttributes = [
            'owner' => $owner,
            'territory' => $territory,
        ];
        ProjectFactory::createMany(
            $searchCount,
            array_merge([$param => $searchValue], $baseAttributes)
        );
        ProjectFactory::createOne(array_merge([$param => $otherValue], $baseAttributes));

        $valueName = $this->getString($searchValue);

        $uri = self::BASE_URI."?$param=$valueName";
        $client = static::createClient();
        $client->request($this->getMethod(), $uri, $this->getRequestOptions($client));

        $this->assertResponseStatusCodeSame($responseCode);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount($searchCount, $responseData['member']);
    }

    private function testGetAllByParamList(
        string $param,
        array $searchValues,
        string|ProjectStatus $otherValue,
    ) {
        $owner = $this->createTestUser();
        $territory = new Territory('ES');
        $baseAttributes = [
            'owner' => $owner,
            'territory' => $territory,
        ];
        foreach ($searchValues as $searchValue) {
            ProjectFactory::createOne(array_merge([$param => $searchValue], $baseAttributes));
        }
        ProjectFactory::createOne(array_merge([$param => $otherValue], $baseAttributes));

        foreach ($searchValues as $searchValue) {
            $valueNames[] = $this->getString($searchValue);
        }

        $client = static::createClient();
        $queryParams = $this->buildQueryParams($param, $valueNames);
        $options = array_merge($this->getRequestOptions($client), $queryParams);
        $client->request($this->getMethod(), $this->getUri(), $options);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(count($searchValues), $responseData['member']);
    }

    // Runable Tests

    public function testGetEmptyCollection()
    {
        static::createClient()->request($this->getMethod(), $this->getUri());

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => '/v4/projects']);
        $this->assertJsonContains(['@type' => 'Collection']);
        $this->assertJsonContains(['totalItems' => 0]);
        $this->assertJsonContains(['member' => []]);
    }

    public function testGetCollection(): void
    {
        $status = ProjectStatus::InEditing;
        $attributes = [
            'title' => 'Test Project',
            'status' => $status,
            'rewards' => [],
        ];
        $this->createTestProjectOptimized(1, $attributes);

        static::createClient()->request($this->getMethod(), $this->getUri());

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['totalItems' => 1]);
        $this->assertJsonContains(['member' => [
            array_merge($attributes, ['status' => $status->value]),
        ]]);
    }

    public function testGetAllSuccessful(): void
    {
        $owner = $this->createTestUser();
        $numberOfProjects = 2;
        ProjectFactory::createMany($numberOfProjects, ['owner' => $owner]);

        $client = static::createClient();
        $client->request(
            $this->getMethod(),
            self::BASE_URI,
            $this->getRequestOptions($client)
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount($numberOfProjects, $responseData['member']);
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
            $this->getMethod(),
            self::BASE_URI."?page=$page&itemsPerPage=$itemsPerPage",
            $this->getRequestOptions($client)
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($totalNumberOfProjects, $responseData['totalItems']);
        $this->assertCount(max($numberOfProjectsInPage, 0), $responseData['member']);
    }

    public function testGetAllDefaultsFirstPage(): void
    {
        $itemsPerPage = 2;
        $totalNumberOfProjects = $itemsPerPage + 1;

        $this->createTestProjectOptimized($totalNumberOfProjects);

        $client = static::createClient();
        $client->request(
            $this->getMethod(),
            self::BASE_URI."?itemsPerPage=$itemsPerPage",
            $this->getRequestOptions($client)
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($totalNumberOfProjects, $responseData['totalItems']);
        $this->assertCount($itemsPerPage, $responseData['member']);
    }

    public function testGetAllByTitle()
    {
        $this->testGetAllByParam('title', 'Free Software Project', 'Education');
    }

    public function testGetAllByCategoryWithInvalidCategory()
    {
        $this->createTestUser();

        $category = 'invalid_category';
        $uri = self::BASE_URI."?category=$category";
        $client = static::createClient();
        $client->request($this->getMethod(), $uri, $this->getRequestOptions($client));

        $responseCode = Response::HTTP_OK;
        $this->assertResponseStatusCodeSame($responseCode);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(0, $responseData['member']);
    }

    public function testGetAllByTitleNotFound()
    {
        ProjectFactory::createOne([
            'owner' => $this->createTestUser(),
            'territory' => new Territory('ES'),
            'title' => 'Lorem ipsum title',
        ]);

        $uri = self::BASE_URI.'?title=NotFound';
        $client = static::createClient();
        $client->request($this->getMethod(), $uri, $this->getRequestOptions($client));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(0, $responseData['member']);
    }

    public function testGetAllByStatus()
    {
        $this->testGetAllByParam('status', ProjectStatus::InFunding, ProjectStatus::InCampaign);
    }

    public function testGetAllByStatusInEditing()
    {
        $this->testGetAllByParam('status', ProjectStatus::InEditing, ProjectStatus::InCampaign);
    }

    public function testGetAllByStatusFulfilled()
    {
        $this->testGetAllByParam('status', ProjectStatus::Funded, ProjectStatus::InCampaign);
    }

    public function testGetAllByStatusList()
    {
        $searchValues = [ProjectStatus::InCampaign, ProjectStatus::Funded];
        $this->testGetAllByParamList('status', $searchValues, ProjectStatus::Rejected);
    }

    public function testGetAllByPartialDescription()
    {
        $partialDescription = 'Physically defined as a modulable space';

        $this->testGetAllByParam(
            'description',
            "lorem ipsum $partialDescription",
            'lorem ipsum vitae'
        );
    }

    public function testGetAllLocalesFieldReturned()
    {
        $this->createTestProjectOptimized(1);

        $client = static::createClient();
        $client->request('GET', self::BASE_URI, $this->getRequestOptions($client));

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $project = $responseData['member'][0];

        $localesKey = 'locales';
        $this->assertArrayHasKey($localesKey, $project);

        $locales = $project[$localesKey];
        $this->assertIsArray($locales);
        foreach ($locales as $locale) {
            $this->assertIsString($locale);
        }
    }

    public function testGetAllUnauthorized(): void
    {
        static::createClient()->request($this->getMethod(), self::BASE_URI);

        $this->assertResponseIsSuccessful();
    }

    public function testGetAllWithInvalidToken(): void
    {
        static::createClient()->request(
            $this->getMethod(),
            self::BASE_URI,
            ['headers' => ['Authorization' => 'Bearer 123']]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
