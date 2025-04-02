<?php

namespace App\Factory\Project;

use App\Entity\Gateway\Checkout;
use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Gateway\Wallet\WalletGateway;
use App\Gateway\Wallet\WalletService;
use App\Library\Economy\MoneyService;
use App\Service\Gateway\CheckoutService;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

use function Zenstruck\Foundry\faker;

/**
 * @extends PersistentProxyObjectFactory<Project>
 */
final class GatewayCheckoutFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(
        private WalletService $walletService,
        private MoneyService $moneyService,
        private CheckoutService $checkoutService,
        private EntityManagerInterface $entityManager,
    ) {}

    public static function class(): string
    {
        return Checkout::class;
    }

    private function createGateway()
    {
        return new WalletGateway(
            $this->walletService,
            $this->moneyService,
            $this->checkoutService,
            $this->entityManager
        );
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $gateway = $this->createGateway();
        $user = new User();
        $project = new Project();

        return [
            'gateway' => $gateway,
            'origin' => $user->getAccounting(),
            'charges' => [
                'type' => 'single',
                'title' => faker()->title(),
                'target' => $project->getAccounting(),
                'money' => [
                    'amount' => faker()->randomNumber(4),
                    'currency' => 'EUR',
                ],
                'description' => faker()->words(),
            ],
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
