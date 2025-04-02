<?php

namespace App\Tests\Entity\GatewayApi\Gateway;

use Symfony\Component\HttpFoundation\Response;

class GetOneTest extends BaseGetTest
{
    private const VALID_NAME = 'paypal';

    // Auxiliary functions

    private function getUri(string $name = self::VALID_NAME)
    {
        return self::BASE_URI."/{$name}";
    }

    private function makeGetOneRequest(
        string $name,
        $expectedCode = Response::HTTP_OK,
        bool $createUser = true,
    ): mixed {
        return $this->makeGetRequest($this->getUri($name), $expectedCode, $createUser);
    }

    // private function makeGetOneRequests(array $names, int $expectedCode): void
    // {
    //     $this->createTestUser();

    //     foreach ($names as $name) {
    //         $this->makeGetOneRequest($name, $expectedCode, false);
    //     }
    // }

    private function makeGetOneRequests(array $namesWithResponseCodeMap): void
    {
        $this->createTestUser();

        foreach ($namesWithResponseCodeMap as $name => $expectedCode) {
            $this->makeGetOneRequest($name, $expectedCode, false);
        }
    }

    // Runable Tests

    public function testGetOneSuccessful()
    {
        $client = $this->makeGetOneRequest(self::VALID_NAME);
        $responseData = $this->getResponseData($client);

        $this->assertGatewayIsCorrect($responseData);
    }

    public function testGetOneWithInvalidNames()
    {
        $expectedCode = Response::HTTP_NOT_FOUND;
        $namesWithResponseCodeMap = [
            'test' => $expectedCode,
            '""' => $expectedCode,
            'null' => $expectedCode,
        ];

        $this->makeGetOneRequests($namesWithResponseCodeMap);
    }

    public function testGetOneWithSpecialCharacters()
    {
        $namesWithResponseCodeMap = [
            '!@#$%^&*()' => Response::HTTP_NOT_FOUND,

            // It should give a 400, but it seems that
            // Symfony internally transforms special characters
            '$%&' => Response::HTTP_NOT_FOUND,
        ];

        $this->makeGetOneRequests($namesWithResponseCodeMap);
    }

    public function testGetOneWithInvalidToken()
    {
        $this->baseTestGetWithInvalidToken($this->getUri());
    }

    public function testGetOneWithoutToken()
    {
        $this->baseTestGetWithoutToken($this->getUri());
    }
}
