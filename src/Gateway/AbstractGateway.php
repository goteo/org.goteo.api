<?php

namespace App\Gateway;

use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Repository\Gateway\CheckoutRepository;
use App\Service\Gateway\CheckoutService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractGateway implements GatewayInterface
{
    public const CHECKOUT_NOT_SPECIFIED = 'The request did not refer to any Checkout';
    public const CHECKOUT_NOT_FOUND = 'Checkout with ID \'%s\' could not be found.';

    protected CheckoutService $checkoutService;
    protected CheckoutRepository $checkoutRepository;
    protected EntityManagerInterface $entityManager;

    #[Required]
    public function setCheckoutService(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    #[Required]
    public function setCheckoutRepository(CheckoutRepository $checkoutRepository)
    {
        $this->checkoutRepository = $checkoutRepository;
    }

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return RedirectResponse loaded with the `returnUrl` of the given Checkout
     */
    public function getRedirectResponse(Checkout $checkout): RedirectResponse
    {
        return new RedirectResponse(
            \sprintf(
                '%s?%s',
                $checkout->getReturnUrl(),
                \http_build_query([
                    'checkoutId' => $checkout->getId(),
                ])
            ),
            Response::HTTP_FOUND
        );
    }

    /**
     * Get the Checkout after a redirection from the gateway.
     *
     * @param Request $request The Request resulting from the user being redirected
     *
     * @throws \Exception If the Request did not contain info on any existing Checkout
     */
    protected function getAfterRedirectCheckout(Request $request): Checkout
    {
        $checkoutId = $request->query->get('checkoutId');
        if ($checkoutId === null) {
            throw new \Exception(self::CHECKOUT_NOT_SPECIFIED);
        }

        $checkout = $this->checkoutRepository->find($checkoutId);

        if ($checkout === null) {
            throw new \Exception(sprintf(self::CHECKOUT_NOT_FOUND, $checkoutId));
        }

        return $checkout;
    }

    public function processRefund(Charge $charge): void
    {
        throw new \LogicException(sprintf(
            'The refund operation is not implemented for the %s gateway.',
            static::getName()
        ));
    }
}
