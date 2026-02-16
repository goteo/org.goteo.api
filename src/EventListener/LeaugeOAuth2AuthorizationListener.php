<?php

namespace App\EventListener;

use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LeaugeOAuth2AuthorizationListener
{
    public const string AUTHORIZATION_RESULT = 'oauth2.authorization_result';

    public function __construct(
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    #[AsEventListener(event: OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE)]
    public function onAuthorizationRequestResolve(AuthorizationRequestResolveEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $authorization = $request->getSession()->get(self::AUTHORIZATION_RESULT, false);

        if (!$authorization) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_consent')));

            return;
        }

        $event->resolveAuthorization($authorization);
        $request->getSession()->remove(self::AUTHORIZATION_RESULT);
    }
}
