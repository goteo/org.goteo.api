<?php

namespace App\Factory\Gateway;

use App\Entity\Gateway\Checkout;
use App\Entity\Project\Project;
use App\Factory\User\UserFactory;
use App\Gateway\CheckoutStatus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Project>
 */
final class CheckoutFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    public static function class(): string
    {
        return Checkout::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $originUser = UserFactory::createOne([
            'handle' => 'checkout_user',
            'email' => 'checkoutuser@checkout.com',
        ]);
        $origin = $originUser->getAccounting();

        $charge = ChargeFactory::createOne();

        return [
            'gatewayName' => 'stripe',
            'origin' => $origin,
            'charges' => [$charge],
            'status' => CheckoutStatus::Pending,
            'returnUrl' => 'https://example.com/success',
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Project $project): void {})
        ;
    }
}
