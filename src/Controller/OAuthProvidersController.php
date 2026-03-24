<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/oauth_providers')]
final class OAuthProvidersController extends AbstractController
{
    public function __construct(
        private ClientRegistry $clientRegistry,
    ) {}

    #[Route('/start/{provider}', name: 'oauth_providers_start')]
    public function authorizationStart(string $provider): Response
    {
        return $this->clientRegistry->getClient($provider)->redirect([], []);
    }

    #[Route('/callback/{provider}', name: 'oauth_providers_callback')]
    public function authorizationCallback(string $provider): Response
    {
        $client = $this->clientRegistry->getClient($provider);

        $accessToken = $client->getAccessToken();
        $user = $client->fetchUserFromToken($accessToken);

        return $this->json([
            'user' => $user,
        ]);
    }
}
