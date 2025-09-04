<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\ApiResource\User\UserTokenApiResource;
use App\Service\Auth\AuthService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class AuthorizationTokenSubscriber implements EventSubscriberInterface
{
    public function __construct() {}

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onView', EventPriorities::POST_WRITE],
            KernelEvents::RESPONSE => ['onResponse', EventPriorities::POST_RESPOND],
        ];
    }

    private function getCookieTtl(): \DateTimeInterface
    {
        return (new \DateTime())->add(new \DateInterval(\sprintf('PT%dS', AuthService::AUTH_COOKIE_TTL)));
    }

    private function getCookie(string $tokenValue)
    {
        return new Cookie(
            name: AuthService::AUTH_COOKIE_NAME,
            value: $tokenValue,
            expire: $this->getCookieTtl(),
            path: '/',
            secure: false,
            httpOnly: true,
            sameSite: 'Lax'
        );
    }

    public function onView(ViewEvent $event): void
    {
        $resourceClass = $event->getRequest()->attributes->get('_api_resource_class');

        if ($resourceClass !== UserTokenApiResource::class) {
            return;
        }

        $event->getRequest()->attributes->set('authTokenMethod', $event->getRequest()->getMethod());

        if ($event->getRequest()->getMethod() === Request::METHOD_POST) {
            $token = $event->getControllerResult();

            $event->getRequest()->attributes->set('authTokenValue', $token->token);
        }
    }

    public function onResponse(ResponseEvent $event): void
    {
        $token = $event->getRequest()->attributes->get('authTokenValue');
        $method = $event->getRequest()->attributes->get('authTokenMethod');

        if (!$method) {
            return;
        }

        switch ($method) {
            case Request::METHOD_DELETE:
                $event->getResponse()->headers->clearCookie(AuthService::AUTH_COOKIE_NAME);
                break;
            case Request::METHOD_POST:
                $event->getResponse()->headers->setCookie($this->getCookie($token));
                break;
        }
    }
}
