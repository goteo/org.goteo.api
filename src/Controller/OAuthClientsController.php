<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/oauth_clients')]
final class OAuthClientsController extends AbstractController
{
    public function __construct(
        private ClientRegistry $clientRegistry,
    ) {}

    #[Route('/google_start', name: 'oauth_clients_google_start')]
    public function googleStart(): Response
    {
        return $this->clientRegistry->getClient('google')->redirect([], []);
    }

    #[Route('/google_callback', name: 'oauth_clients_google_callback')]
    public function googleCallback(Request $request): Response
    {
        $client = $this->clientRegistry->getClient('google');

        $accessToken = $client->getAccessToken();
        $user = $client->fetchUserFromToken($accessToken);

        return $this->json([
            'body' => $request->getContent(),
            'headers' => $request->headers->all(),
            'query' => $request->query->all(),
            'access_token' => $accessToken,
            'user' => $user
        ]);
    }
}
