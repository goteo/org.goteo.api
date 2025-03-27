<?php

namespace App\Tests\Entity\ProjectApi;

use Symfony\Component\HttpFoundation\Response;

class DeleteTest extends BaseTest
{
    public function testDeleteWithValidToken(): void
    {
        $this->prepareTestProject();

        $client = static::createClient();
        $client->request('DELETE', $this->getUri(), ['headers' => $this->getHeaders($client)]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteUnauthorized()
    {
        $this->prepareTestProject();

        static::createClient()->request('DELETE', $this->getUri());

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
