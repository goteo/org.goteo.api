<?php

namespace App\Gateway\Stripe;

use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Entity\Gateway\Tracking;
use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Gateway\AbstractGateway;
use App\Gateway\ChargeType;
use App\Gateway\CheckoutStatus;
use App\Gateway\Link;
use App\Gateway\LinkType;
use App\Service\Gateway\CheckoutService;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;
use Stripe\Webhook as StripeWebhook;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeGateway extends AbstractGateway
{
    public const TRACKING_TITLE_CHECKOUT = 'Stripe Checkout Session ID';

    private StripeClient $stripe;

    public function __construct(
        private string $stripeApiKey,
        private string $stripeWebhookSecret,
    ) {
        $this->stripe = new StripeClient($stripeApiKey);
    }

    public static function getName(): string
    {
        return 'stripe';
    }

    public static function getSupportedChargeTypes(): array
    {
        return [
            ChargeType::Single,
            ChargeType::Recurring,
        ];
    }

    public function process(Checkout $checkout): Checkout
    {
        $session = $this->stripe->checkout->sessions->create([
            'customer_email' => $this->getStripeCustomer($checkout),
            'mode' => $this->getStripeMode($checkout),
            'line_items' => $this->getStripeLineItems($checkout),
            // Because Symfony's Router encodes query parameters, the value {CHECKOUT_SESSION_ID}
            // is not properly sent to Stripe and the redirection fails,
            // that's why we add the session_id template variable like this.
            // https://docs.stripe.com/payments/checkout/custom-success-page?lang=php#modify-the-success-url
            'success_url' => \sprintf(
                '%s&session_id={CHECKOUT_SESSION_ID}',
                $this->checkoutService->generateRedirectUrl($checkout, CheckoutService::RESPONSE_TYPE_SUCCESS)
            ),
        ]);

        $link = new Link();

        $link->href = $session->url;
        $link->rel = 'approve';
        $link->method = Request::METHOD_GET;
        $link->type = LinkType::Payment;

        $checkout->addLink($link);
        $checkout->addTracking(new Tracking(self::TRACKING_TITLE_CHECKOUT, $session->id));

        return $checkout;
    }

    public function handleRedirect(Request $request): RedirectResponse
    {
        // TO-DO: handle non-success type redirect

        $checkout = $this->getAfterRedirectCheckout($request);
        $redirection = $this->getRedirectResponse($checkout);

        if ($checkout->getStatus() === CheckoutStatus::Charged) {
            return $redirection;
        }

        $sessionId = $request->query->get('session_id');
        $session = $this->stripe->checkout->sessions->retrieve($sessionId);

        if ($session->payment_status !== StripeSession::PAYMENT_STATUS_PAID) {
            return $redirection;
        }

        $checkout = $this->checkoutService->chargeCheckout($checkout);

        $this->entityManager->persist($checkout);
        $this->entityManager->flush();

        return $redirection;
    }

    public function handleWebhook(Request $request): Response
    {
        $webhook = StripeWebhook::constructEvent(
            $request->getContent(),
            $request->headers->get('STRIPE_SIGNATURE'),
            $this->stripeWebhookSecret
        );

        switch ($webhook->type) {
            default:
                return new JsonResponse([
                    'error' => sprintf("The event '%s' is not supported", $webhook->type),
                ], Response::HTTP_BAD_REQUEST);
                break;
        }
    }

    private function getStripeCustomer(Checkout $checkout): string
    {
        $owner = $checkout->getOrigin()->getOwner();
        if (!$owner instanceof User) {
            throw new \Exception(\sprintf(
                "Checkout with Stripe must be performed by an User, instance of '%s' supplied",
                $owner::class
            ));
        }

        return $owner->getEmail();
    }

    private function getStripeMode(Checkout $checkout): string
    {
        foreach ($checkout->getCharges() as $charge) {
            if ($charge->getType() === ChargeType::Recurring) {
                return StripeSession::MODE_SUBSCRIPTION;
            }
        }

        return StripeSession::MODE_PAYMENT;
    }

    private function getStripeLineItems(Checkout $checkout): array
    {
        $items = [];

        foreach ($checkout->getCharges() as $charge) {
            $price = [
                'currency' => $charge->getMoney()->currency,
                'unit_amount' => $charge->getMoney()->amount,
                'product' => $this->getStripeProduct($charge),
                'nickname' => $charge->getTitle(),
            ];

            if ($charge->getType() === ChargeType::Recurring) {
                $price['recurring'] = ['interval' => 'month'];
            }

            $items[] = [
                'quantity' => 1,
                'price' => $this->stripe->prices->create($price)->id,
            ];
        }

        return $items;
    }

    private function getStripeProduct(Charge $charge)
    {
        $target = $charge->getTarget()->getOwner();

        if (!$target instanceof Project) {
            throw new \Exception(\sprintf(
                "Charges with Stripe must be to Projects, instance of '%s' supplied",
                $target::class
            ));
        }

        $id = \sprintf('P%d', $target->getId());

        try {
            $product = $this->stripe->products->retrieve($id);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            if (!$e->getStripeCode() === 'resource_missing') {
                throw $e;
            }

            $name = $target->getTitle();
            $description = $target->getSubtitle();

            $product = $this->stripe->products->create([
                'id' => $id,
                'name' => $name,
                'description' => $description,
            ]);
        }

        return $product;
    }

    public function processRefund(Charge $charge): void
    {
        $trackings = $charge->getCheckout()->getTrackings();
        $sessionTracking = $trackings->filter(function (Tracking $t) {
            return $t->getTitle() === self::TRACKING_TITLE_CHECKOUT;
        })->current();

        if (!$sessionTracking) {
            throw new \Exception('Tracking for Stripe Session ID not found.');
        }

        $sessionId = $sessionTracking->getValue();

        $session = $this->stripe->checkout->sessions->retrieve($sessionId);

        $paymentIntent = $this->stripe->paymentIntents->retrieve($session->payment_intent);

        $this->stripe->refunds->create([
            'payment_intent' => $paymentIntent->id,
            'amount' => $charge->getMoney()->amount,
        ]);

        $this->chargeService->addRefundTransaction($charge);
    }
}
