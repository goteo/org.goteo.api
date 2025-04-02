<?php

namespace App\Tests\Entity\GatewayApi\Gateway;

class GetAllTest extends BaseGetTest
{
    // Auxiliary Tests

    private function baseTestGetAllOnPage(int|string $page): void
    {
        $this->createTestUser();

        $client = static::createClient();
        $uri = self::BASE_URI."?page=$page";
        $client->request(self::METHOD, $uri, $this->getRequestOptions($client));

        // TODO: Correct Asserts when pagination works as expected
        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $gateways = $responseData['member'];

        $this->assertGatewaysAreCorrects($gateways);
    }

    // Runable Tests

    public function testGetAllSuccessful()
    {
        $responseData = $this->getResponseData($this->makeGetRequest());

        $gateways = $responseData['member'];
        $this->assertGatewaysAreCorrects($gateways);
    }

    public function testGetAllEmpty()
    {
        $responseData = $this->getResponseData($this->makeGetRequest());

        if ($responseData['totalItems'] == 0) {
            $this->assertSame([], $responseData['member']);
        }
    }

    public function testGetAllOnPage()
    {
        $this->baseTestGetAllOnPage(2);
    }

    public function testGetAllOnOutOfRangePage()
    {
        $this->baseTestGetAllOnPage(99999);
    }

    public function testGetAllOnInvalidPage()
    {
        $this->baseTestGetAllOnPage('abx');
    }

    public function testGetAllWithInvalidToken()
    {
        $this->baseTestGetWithInvalidToken();
    }

    public function testGetAllWithoutToken()
    {
        $this->baseTestGetWithoutToken();
    }
}
