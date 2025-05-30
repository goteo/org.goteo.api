<?php

namespace App\Gateway\Paypal;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaypalService
{
    private CacheInterface $cache;

    public function __construct(
        private string $paypalApiAddress,
        private string $paypalClientId,
        private string $paypalClientSecret,
        private string $paypalWebhookId,
        private HttpClientInterface $httpClient,
    ) {
        $this->cache = new FilesystemAdapter(\preg_replace(
            '/[^-+_\.A-Za-z0-9]/',
            '-',
            $this->paypalApiAddress
        ));

        $httpOptions = new HttpOptions();
        $httpOptions->setBaseUri($paypalApiAddress);

        $this->setHttpClient($httpClient->withOptions($httpOptions->toArray()));
    }

    public function setHttpClient(HttpClientInterface $httpClient): static
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Calls the PayPal API to generate an OAuth2 token.
     *
     * @return array The API response body
     */
    public function generateAuthToken(): array
    {
        $response = $this->httpClient->request(Request::METHOD_POST, '/v1/oauth2/token', [
            'auth_basic' => [$this->paypalClientId, $this->paypalClientSecret],
            'body' => 'grant_type=client_credentials',
        ]);

        return $response->toArray();
    }

    /**
     * Retrieves the PayPal OAuth2 token data from cache (if available) or generates a new one.
     *
     * @return array The OAuth2 token data
     */
    public function getAuthToken(): array
    {
        return $this->cache->get(urlencode('/v1/oauth2/token'), function (ItemInterface $item): array {
            $tokenData = $this->generateAuthToken();

            $item->expiresAfter($tokenData['expires_in']);

            return $tokenData;
        });
    }

    /**
     * Creates a PayPal Order resource with the given data.
     *
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_create
     */
    public function postOrder(array $order): array
    {
        $response = $this->httpClient->request(Request::METHOD_POST, '/v2/checkout/orders', [
            'auth_bearer' => $this->getAuthToken()['access_token'],
            'json' => $order,
        ]);

        $content = \json_decode($response->getContent(), true);
        if (!\in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            throw new \Exception($content['message']);
        }

        return $content;
    }

    /**
     * Retrieve a PayPal Order resource.
     *
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_get
     */
    public function getOrder(string $orderId): array
    {
        $request = $this->httpClient->request(
            Request::METHOD_GET,
            sprintf('/v2/checkout/orders/%s', $orderId),
            [
                'auth_bearer' => $this->getAuthToken()['access_token'],
            ]
        );

        if ($request->getStatusCode() !== Response::HTTP_OK) {
            throw new \Exception(sprintf("PayPal checkout '%s' could not be requested.", $orderId));
        }

        return \json_decode($request->getContent(), true);
    }

    /**
     * Process post user-approval payment capture for a given Order.
     *
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_capture
     */
    public function captureOrderPayment(array $order): array
    {
        $link = \array_filter($order['links'], function ($order) {
            return $order['rel'] === 'capture';
        });

        if (empty($link)) {
            throw new \Exception(sprintf("PayPal checkout '%s' was not ready for capture.", $order['id']));
        }

        $link = \array_pop($link);
        $request = $this->httpClient->request(
            $link['method'],
            $link['href'],
            [
                'auth_bearer' => $this->getAuthToken()['access_token'],
                'headers' => ['Content-Type' => 'application/json'],
            ]
        );

        if ($request->getStatusCode() !== Response::HTTP_CREATED) {
            throw new \Exception(sprintf("Payment capture for PayPal checkout '%s' was unsuccessful.", $order['id']));
        }

        return \json_decode($request->getContent(), true);
    }

    public function getWebhookVerificationPayload(mixed $headers, mixed $rawBody): string|false
    {
        return json_encode([
            'auth_algo' => $headers->get('paypal-auth-algo'),
            'cert_url' => $headers->get('paypal-cert-url'),
            'transmission_id' => $headers->get('paypal-transmission-id'),
            'transmission_sig' => $headers->get('paypal-transmission-sig'),
            'transmission_time' => $headers->get('paypal-transmission-time'),
            'webhook_id' => $this->paypalWebhookId,
            'webhook_event' => json_decode($rawBody),
        ]);
    }

    /**
     * Verifies a PayPal webhook request.
     *
     * @see https://developer.paypal.com/community/blog/paypal-has-updated-its-webhook-verification-endpoint/
     *
     * @param Request $request The webhook request
     *
     * @return array The webhook event data
     *
     * @throws \Exception If the verification fails
     */
    public function verifyWebhook(Request $request): array
    {
        $rawBody = $request->getContent();

        $response = $this->httpClient->request(
            'POST',
            '/v1/notifications/verify-webhook-signature',
            [
                'auth_bearer' => $this->getAuthToken()['access_token'],
                'headers' => ['Content-Type' => 'application/json'],
                'body' => $this->getWebhookVerificationPayload($request->headers, $rawBody),
            ]
        );

        $data = $response->toArray(false);

        if (($data['verification_status'] ?? '') !== 'SUCCESS') {
            throw new \Exception('Could not verify PayPal webhook signature.');
        }

        return json_decode($rawBody, true);
    }

    public function refundCapture(string $captureId, array $payload): array
    {
        $response = $this->httpClient->request(
            'POST',
            "/v2/payments/captures/{$captureId}/refund",
            [
                'auth_bearer' => $this->getAuthToken()['access_token'],
                'json' => $payload,
            ]
        );

        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            throw new \Exception(sprintf(
                "Refund for capture '%s' failed with status %d.",
                $captureId,
                $response->getStatusCode()
            ));
        }

        return $response->toArray();
    }
}
