<?php

namespace App\Tests\Entity\GatewayApi\Gateway;

use Symfony\Component\HttpFoundation\Response;

class GetOneTest extends BaseGetTest
{
    // Auxiliary functions

    private function makeGetOneRequest(string $name, $expectedCode = Response::HTTP_OK): mixed
    {
        return $this->makeGetRequest(self::BASE_URI."/{$name}", $expectedCode);
    }

    // Runable Tests

    public function testGetOneSuccessful()
    {
        $client = $this->makeGetOneRequest('paypal');
        $responseData = $this->getResponseData($client);

        $this->assertGatewayIsCorrect($responseData);
    }

    public function testGetOneInvalidName()
    {
        $this->makeGetOneRequest('test', Response::HTTP_NOT_FOUND);
    }
}
