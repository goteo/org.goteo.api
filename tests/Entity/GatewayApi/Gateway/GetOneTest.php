<?php

namespace App\Tests\Entity\GatewayApi\Gateway;

use Symfony\Component\HttpFoundation\Response;

class GetOneTest extends BaseGetTest
{
    private const VALID_NAME = 'paypal';

    // Auxiliary functions

    private function getUri(string $name)
    {
        return self::BASE_URI."/{$name}";
    }

    private function makeGetOneRequest(string $name, $expectedCode = Response::HTTP_OK): mixed
    {
        return $this->makeGetRequest($this->getUri($name), $expectedCode);
    }

    // Runable Tests

    public function testGetOneSuccessful()
    {
        $client = $this->makeGetOneRequest(self::VALID_NAME);
        $responseData = $this->getResponseData($client);

        $this->assertGatewayIsCorrect($responseData);
    }

    public function testGetOneInvalidName()
    {
        $this->makeGetOneRequest('test', Response::HTTP_NOT_FOUND);
    }

    public function testGetOneWithInvalidToken()
    {
        $this->baseTestWithInvalidToken($this->getUri(self::VALID_NAME));
    }
}
