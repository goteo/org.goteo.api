<?php

namespace App\Tests\Entity\GatewayApi\Gateway;

class GetOneTest extends BaseGetTest
{
    // Auxiliary functions

    private function makeGetOneRequest(string $name): mixed
    {
        return $this->makeGetRequest(self::BASE_URI."/{$name}");
    }

    // Runable Tests

    public function testGetOneSuccessful()
    {
        $responseData = $this->makeGetOneRequest('paypal');

        $this->assertGatewayIsCorrect($responseData);
    }
}
