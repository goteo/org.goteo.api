<?php

namespace App\Tests\Entity\GatewayApi\GatewayCheckout;

use Symfony\Component\HttpFoundation\Response;

class GetOneTest extends BaseTestCase
{
    protected const METHOD = 'GET';

    // Auxiliary functions

    private function getUri(int|string $id = 1): string
    {
        return self::BASE_URI."/{$id}";
    }

    private function makeRequest(int|string $id = 1)
    {
        $client = static::createClient();
        $client->request(
            self::METHOD,
            $this->getUri($id),
            ['headers' => $this->getAuthHeaders($client, self::USER_EMAIL, self::USER_PASSWORD)]
        );

        return $client;
    }

    // Runable Tests

    public function testGetOneSuccessful()
    {
        $client = $this->makeRequest(1);

        $this->assertResponseIsSuccessful();

        $responseData = $this->getResponseData($client);
        $this->assertCheckoutIsCorrect($responseData);
    }

    public function testGetOneWithNotFoundId()
    {
        $this->makeRequest(99999);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetOneWithInvalidId()
    {
        $this->makeRequest('abc123');

        // Probably must give a 400
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetOneWithInvalidToken()
    {
        $this->testInvalidToken(self::METHOD, $this->getUri(1));
    }

    public function testGetOneWithoutAccessToken()
    {
        static::createClient()->request(self::METHOD, $this->getUri(1));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
