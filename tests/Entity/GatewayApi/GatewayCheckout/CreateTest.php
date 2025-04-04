<?php

namespace App\Tests\Entity\GatewayApi\GatewayCheckout;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\Response;

class CreateTest extends BaseTest
{
    protected const METHOD = 'POST';

    // Auxiliary functions

    private function makeRequest($data): Client
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

    // Runable tests

    public function testCreateSuccessful()
    {
        $data = [
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

        $client = $this->makeRequest($data);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = $this->getResponseData($client);
        $this->assertCheckoutIsCorrect($responseData);
    }
}
