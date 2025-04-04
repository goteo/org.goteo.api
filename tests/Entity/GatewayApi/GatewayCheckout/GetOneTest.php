<?php

namespace App\Tests\Entity\GatewayApi\GatewayCheckout;

use Symfony\Component\HttpFoundation\Response;

class GetOneTest extends GetBaseTest
{
    // Auxiliary functions

    private function getUri(int $id = 1)
    {
        return self::BASE_URI."/{$id}";
    }

    private function makeRequest(int $id = 1)
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

    public function testGetOneWithInvalidId()
    {
        $this->makeRequest(99999);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
