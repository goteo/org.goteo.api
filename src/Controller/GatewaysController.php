<?php

namespace App\Controller;

use App\Gateway\GatewayLocator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The `GatewaysController` exposes the handler routes for responding to network requests sent by external gateways.
 */
#[Route('/v4/controllers')]
class GatewaysController extends AbstractController
{
    public const REDIRECT = 'gateway_controller.redirect';
    public const WEBHOOKS = 'gateway_controller.webhooks';

    public function __construct(
        private GatewayLocator $gatewayLocator,
    ) {}

    #[Route('/gateway_redirects', name: self::REDIRECT)]
    public function handleRedirect(Request $request): Response
    {
        $gateway = $this->gatewayLocator->get($request->query->get('gateway'));

        return $gateway->handleRedirect($request);
    }

    #[Route('/gateway_webhooks/{gateway}', name: self::WEBHOOKS)]
    public function handleWebhook(Request $request, string $gateway): Response
    {
        $handler = $this->gatewayLocator->get($gateway);

        return $handler->handleWebhook($request);
    }
}
