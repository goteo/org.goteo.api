<?php

namespace App\Tests\Entity\GatewayApi\GatewayCheckout;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\Response;

class CreateTest extends BaseTest
{
    protected const METHOD = 'POST';

    private const DATA = [
        'gateway' => '/v4/gateways/stripe',
        'origin' => '/v4/accountings/1',
        'charges' => [
            [
                'type' => 'single',
                'title' => 'Charge 1',
                'target' => '/v4/accountings/3',
                'money' => [
                    'amount' => 100,
                    'currency' => 'USD',
                ],
                'description' => 'description',
            ],
        ],
        'returnUrl' => 'https://example.com/return',
    ];

    // Auxiliary functions

    private function makeRequest(array $data = self::DATA): Client
    {
        $client = static::createClient();
        $client->request(
            self::METHOD,
            self::BASE_URI,
            [
                'headers' => $this->getAuthHeaders($client, self::USER_EMAIL, self::USER_PASSWORD),
                'json' => $data,
            ]
        );

        return $client;
    }

    // Auxiliary tests

    private function baseTestCreateWithOverride(array $override, int $expectedCode = Response::HTTP_CREATED)
    {
        $data = array_merge(self::DATA, $override);

        $this->makeRequest($data);

        $this->assertResponseStatusCodeSame($expectedCode);
    }

    private function baseTestCreateWithEmptyMandatoryField(string $field)
    {
        $this->baseTestCreateWithOverride([$field => ''], Response::HTTP_BAD_REQUEST);
    }

    // Runable tests

    public function testCreateSuccessful()
    {
        $client = $this->makeRequest();

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = $this->getResponseData($client);
        $this->assertCheckoutIsCorrect($responseData);
    }

    public function testCreateWithEmptyGatewayField()
    {
        $this->baseTestCreateWithEmptyMandatoryField('gateway');
    }

    public function testCreateWithEmptyOriginField()
    {
        $this->baseTestCreateWithEmptyMandatoryField('origin');
    }

    public function testCreateWithEmptyChargesField()
    {
        $this->baseTestCreateWithEmptyMandatoryField('charges');
    }

    public function testCreateWithNullGateway()
    {
        $this->baseTestCreateWithOverride(['gateway' => null], Response::HTTP_BAD_REQUEST);
    }

    public function testCreateWithInvalidURL()
    {
        $expectedCode = Response::HTTP_UNPROCESSABLE_ENTITY;
        $this->baseTestCreateWithOverride(['returnUrl' => 'invalid-url'], $expectedCode);
    }

    public function testCreateWithInvalidCharge()
    {
        $override = [
            'charges' => [
                [
                    'type' => 'invalid_type',
                ],
            ],
        ];

        $this->baseTestCreateWithOverride($override, Response::HTTP_BAD_REQUEST);
    }

    public function testCreateWithInvalidToken()
    {
        $this->testInvalidToken(self::METHOD, self::BASE_URI);
    }
}
