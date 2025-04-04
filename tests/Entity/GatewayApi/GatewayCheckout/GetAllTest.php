<?php

namespace App\Tests\Entity\GatewayApi\GatewayCheckout;

use Symfony\Component\HttpFoundation\Response;

class GetAllTest extends GetBaseTest
{
    // Auxiliary functions

    private function getUri(?int $page = null)
    {
        $pageParam = $page == null ? '' : "?page={$page}";

        return self::BASE_URI.$pageParam;
    }

    private function makeRequest(?int $page = null)
    {
        $client = static::createClient();
        $client->request(
            self::METHOD,
            $this->getUri($page),
            ['headers' => $this->getAuthHeaders($client, self::USER_EMAIL, self::USER_PASSWORD)]
        );

        return $client;
    }

    // Runable tests

    public function testGetAllSuccessful()
    {
        $client = $this->makeRequest(1);

        $this->assertResponseIsSuccessful();

        $responseData = $this->getResponseData($client);
        $this->assertCheckoutIsCorrect($responseData['member'][0]);
    }

    public function testGetAllDefaultsFirstPage()
    {
        $client = $this->makeRequest();

        $this->assertResponseIsSuccessful();

        $responseData = $this->getResponseData($client);
        $this->assertCount(self::PAGE_SIZE, $responseData['member']);
    }

    public function testGetAllNoResultsOnEmptyPage()
    {
        $client = $this->makeRequest(self::PAGES_TO_FILL + 2);

        $this->assertResponseIsSuccessful();

        $responseData = $this->getResponseData($client);
        $this->assertEmpty($responseData['member']);
    }

    public function testGetAllWithInvalidPage()
    {
        $this->makeRequest(-1);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetAllWithInvalidToken()
    {
        $this->testInvalidToken(self::METHOD, self::BASE_URI);
    }

    public function testGetAllWithoutAccessToken()
    {
        static::createClient()->request(self::METHOD, self::BASE_URI);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
