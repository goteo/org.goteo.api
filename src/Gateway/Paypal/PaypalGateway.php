<?php

namespace App\Gateway\Paypal;

use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Gateway\AbstractGateway;
use App\Gateway\ChargeType;
use App\Gateway\CheckoutStatus;
use App\Gateway\Link;
use App\Gateway\LinkType;
use App\Gateway\Tracking;
use Brick\Money\Money as BrickMoney;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see https://developer.paypal.com/studio/checkout/standard/integrate
 */
class PaypalGateway extends AbstractGateway
{
    public const PAYPAL_API_ADDRESS_LIVE = 'https://api-m.paypal.com';
    public const PAYPAL_API_ADDRESS_SANDBOX = 'https://api-m.sandbox.paypal.com';

    /** @see https://developer.paypal.com/docs/api/orders/v2/#orders_get!c=200&path=status&t=response */
    public const PAYPAL_STATUS_APPROVED = 'APPROVED';
    public const PAYPAL_STATUS_COMPLETED = 'COMPLETED';

    /** @see https://developer.paypal.com/api/rest/webhooks/event-names/#orders */
    public const PAYPAL_EVENT_ORDER_COMPLETED = 'CHECKOUT.ORDER.COMPLETED';

    /**
     * @see https://developer.paypal.com/docs/api/orders/v2/
     */
    private const PAYPAL_ORDER_INTENT = 'CAPTURE';

    public const TRACKING_TITLE_ORDER = 'PayPal Order ID';
    public const TRACKING_TITLE_TRANSACTION = 'PayPal Transaction ID';

    public function __construct(
        private PaypalService $paypal,
    ) {}

    public static function getName(): string
    {
        return 'paypal';
    }

    public static function getSupportedChargeTypes(): array
    {
        return [
            ChargeType::Single,
        ];
    }

    public function process(Checkout $checkout): Checkout
    {
        $order = $this->paypal->postOrder([
            'intent' => self::PAYPAL_ORDER_INTENT,
            'purchase_units' => $this->getPaypalPurchaseUnits($checkout),
            'payment_source' => $this->getPaypalPaymentSource($checkout),
        ]);

        $tracking = new Tracking();
        $tracking->title = self::TRACKING_TITLE_ORDER;
        $tracking->value = $order['id'];

        $checkout->addTracking($tracking);

        foreach ($order['links'] as $linkData) {
            $linkType = \in_array($linkData['rel'], ['approve', 'payer-action'])
                ? LinkType::Payment
                : LinkType::Debug;

            $link = new Link();
            $link->href = $linkData['href'];
            $link->rel = $linkData['rel'];
            $link->method = $linkData['method'];
            $link->type = $linkType;

            $checkout->addLink($link);
        }

        return $checkout;
    }

    public function handleRedirect(Request $request): RedirectResponse
    {
        // TO-DO: handle non-success type redirect requests

        $checkout = $this->getAfterRedirectCheckout($request);
        $redirection = $this->getRedirectResponse($checkout);

        if ($checkout->getStatus() === CheckoutStatus::Charged) {
            return $redirection;
        }

        $orderId = $request->query->get('token');
        $order = $this->paypal->getOrder($orderId);

        if ($order['status'] !== self::PAYPAL_STATUS_APPROVED) {
            return $redirection;
        }

        $capture = $this->paypal->captureOrderPayment($order);
        if ($capture['status'] !== self::PAYPAL_STATUS_COMPLETED) {
            return $redirection;
        }

        foreach ($capture['purchase_units'] as $purchaseUnit) {
            $tracking = new Tracking();
            $tracking->title = self::TRACKING_TITLE_TRANSACTION;
            $tracking->value = $purchaseUnit['payments']['captures'][0]['id'];

            $checkout->addTracking($tracking);
        }

        $checkout = $this->checkoutService->chargeCheckout($checkout);

        $this->entityManager->persist($checkout);
        $this->entityManager->flush();

        return $redirection;
    }

    public function handleWebhook(Request $request): Response
    {
        try {
            $event = $this->paypal->verifyWebhook($request);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'ERROR',
                'message' => $e->getMessage(),
                'requestBody' => $request->getContent(),
                'requestHeaders' => $request->headers->all(),
            ], Response::HTTP_ACCEPTED);
        }

        switch ($event['event_type']) {
            case self::PAYPAL_EVENT_ORDER_COMPLETED:
                return $this->handleOrderCompleted($event);
            default:
                return new Response('Event not supported', Response::HTTP_ACCEPTED);
        }

        return new Response();
    }

    private function handleOrderCompleted(array $event)
    {
        $orderId = $event['resource']['id'];

        $checkout = $this->checkoutRepository->findOneByTracking(self::TRACKING_TITLE_ORDER, $orderId);

        if ($checkout === null) {
            throw new \Exception(sprintf("Could not find any Checkout by the Tracking '%s'", $orderId), 1);
        }

        foreach ($event['resource']['purchase_units'] as $purchaseUnit) {
            $tracking = new Tracking();
            $tracking->title = self::TRACKING_TITLE_TRANSACTION;
            $tracking->value = $purchaseUnit['payments']['captures'][0]['id'];

            $checkout->addTracking($tracking);
        }

        $checkout = $this->checkoutService->chargeCheckout($checkout);
    }

    private function getPaypalMoney(Charge $charge): array
    {
        $brick = BrickMoney::ofMinor(
            $charge->getMoney()->amount,
            $charge->getMoney()->currency
        );

        return [
            'value' => $brick->getAmount()->__toString(),
            'currency_code' => $brick->getCurrency()->getCurrencyCode(),
        ];
    }

    private function getPaypalPurchaseUnits(Checkout $checkout): array
    {
        $units = [];

        foreach ($checkout->getCharges() as $charge) {
            $money = $this->getPaypalMoney($charge);
            $reference = $this->checkoutService->generateTracking($checkout, $charge);

            $units[] = [
                'reference_id' => $reference,
                'custom_id' => $reference,
                'items' => [
                    [
                        'name' => $charge->getTitle(),
                        'description' => $charge->getDescription(),
                        'quantity' => '1',
                        'unit_amount' => [
                            ...$money,
                        ],
                    ],
                ],
                'amount' => [
                    ...$money,
                    'breakdown' => [
                        'item_total' => [
                            ...$money,
                        ],
                    ],
                ],
            ];
        }

        return $units;
    }

    private function getPaypalPaymentSource(Checkout $checkout): array
    {
        return [
            'paypal' => [
                'experience_context' => [
                    'return_url' => $this->checkoutService->generateRedirectUrl($checkout),
                ],
            ],
        ];
    }
}
