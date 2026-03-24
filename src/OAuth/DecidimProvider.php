<?php

namespace App\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class DecidimProvider extends AbstractProvider
{
    private string $providerUri;

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
        throw new \Exception('Not implemented');
    }

    public function getDefaultScopes()
    {
        return ['public'];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        dd($response, $data);
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        dd($response, $token);
    }
}
