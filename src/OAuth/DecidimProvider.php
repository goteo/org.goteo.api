<?php

namespace App\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DecidimProvider extends AbstractProvider
{
    private string $providerUri;

    private string $providerClient;

    /**
     * @param array{url: string, clientId: string, clientSecret: string, redirectUri: string} $options
     */
    public function __construct(
        array $options = [],
        array $collaborators = [],
    ) {
        if (!\array_key_exists('url', $options)) {
            throw new \Exception("Key 'url' must be given in the options array");
        }

        $this->providerUri = $options['url'];
        $this->providerClient = $options['clientId'];

        return parent::__construct($options, $collaborators);
    }

    public function getBaseAuthorizationUrl()
    {
        return \sprintf('%s/oauth/authorize', $this->providerUri);
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return \sprintf('%s/oauth/token', $this->providerUri);
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return \sprintf('%s/api', $this->providerUri);
    }

    public function getDefaultScopes()
    {
        return [
            'user',
        ];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        $code = $response->getStatusCode();
        if ($code === Response::HTTP_OK) {
            return;
        }

        throw new IdentityProviderException('The instance returned a non-OK HTTP response', $code, $response);
    }

    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $accessToken = $token->getToken();

        $url = $this->getResourceOwnerDetailsUrl($token);

        $request = $this->getRequest(Request::METHOD_POST, $url, [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
                'X-Jwt-Aud' => $this->providerClient,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'body' => \json_encode(['query' => '{ session { user { id name nickname } } }']),
        ]);

        $response = $this->getResponse($request);
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        dd($response, $token);
    }
}
