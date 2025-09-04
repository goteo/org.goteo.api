<?php

namespace App\Benzina;

use App\Entity\Accounting\Transaction;
use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Entity\Gateway\Tracking;
use App\Entity\Money;
use App\Entity\Project\Project;
use App\Entity\Tipjar;
use App\Entity\User\User;
use App\Gateway\ChargeStatus;
use App\Gateway\ChargeType;
use App\Gateway\CheckoutStatus;
use App\Gateway\Gateway\CashGateway;
use App\Gateway\Gateway\CecaGateway;
use App\Gateway\Gateway\DropGateway;
use App\Gateway\Paypal\PaypalGateway;
use App\Gateway\Stripe\StripeGateway;
use App\Gateway\Wallet\WalletGateway;
use App\Repository\Project\ProjectRepository;
use App\Repository\TipjarRepository;
use App\Repository\User\UserRepository;
use App\Service\Gateway\CheckoutService;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;
use Goteo\Benzina\Pump\PumpInterface;

class CheckoutsPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;
    use CheckoutsPumpTrait;

    public const TRACKING_TITLE_V3 = 'v3 Invest ID';
    public const TRACKING_TITLE_PAYMENT = 'v3 Invest Payment';
    public const TRACKING_TITLE_TRANSACTION = 'v3 Invest Transaction';
    public const TRACKING_TITLE_PREAPPROVAL = 'v3 Invest Preapproval';

    public const CHARGE_TITLE_PROJECT = 'Pago en Goteo v3 - Donación a proyecto';
    public const CHARGE_TITLE_POOL = 'Pago en Goteo v3 - Carga de monedero';
    public const CHARGE_TITLE_TIP = 'Pago en Goteo v3 - Propina a la plataforma';

    private const PLATFORM_RETURN_URL = 'https://goteo.org';
    private const PLATFORM_TIPJAR_NAME = 'platform';

    private const MAX_INT = 2147483647;
    private const CURRENCY = 'EUR';

    public function __construct(
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
        private TipjarRepository $tipjarRepository,
        private CheckoutService $checkoutService,
    ) {}

    public function supports(mixed $sample): bool
    {
        if ($this->hasAllKeys($sample, self::INVEST_KEYS)) {
            return true;
        }

        return false;
    }

    public function pump(mixed $record, array $context): void
    {
        if (!$record['user'] || empty($record['user'])) {
            return;
        }

        if (!$record['amount'] || $record['amount'] < 1) {
            return;
        }

        if (!$record['method'] || empty($record['method'])) {
            return;
        }

        $user = $this->getUser($record);
        if ($user === null) {
            return;
        }

        $project = $this->getProject($record);
        $tipjar = $this->getPlatformTipjar();
        $invested = new \DateTime($record['invested']);

        $checkout = new Checkout();
        $checkout->setMigrated(true);
        $checkout->setMigratedId($record['id']);
        $checkout->setDateCreated($invested);
        $checkout->setDateUpdated(new \DateTime());

        $checkout->setOrigin($user->getAccounting());
        $checkout->setStatus($this->getCheckoutStatus($record));
        $checkout->setGatewayName($this->getCheckoutGateway($record));
        $checkout->setReturnUrl(self::PLATFORM_RETURN_URL);

        foreach ($this->getCheckoutTrackings($record) as $tracking) {
            $checkout->addTracking($tracking);
        }

        $charge = new Charge();
        $charge->setDateCreated($invested);
        $charge->setDateUpdated(new \DateTime());
        $charge->setStatus($this->getChargeStatus($record));
        $charge->setType($this->getChargeType($record));
        $charge->setMoney($this->getChargeMoney($record['amount'], self::CURRENCY));

        if ($project === null) {
            $charge->setTitle(self::CHARGE_TITLE_POOL);
            $charge->setTarget($user->getAccounting());
        } else {
            $charge->setTitle(self::CHARGE_TITLE_PROJECT);
            $charge->setTarget($project->getAccounting());
        }

        $checkout->addCharge($charge);

        if ($record['donate_amount'] > 0) {
            $tip = new Charge();
            $tip->setDateCreated($invested);
            $tip->setDateUpdated(new \DateTime());
            $tip->setStatus($this->getChargeStatus($record));
            $tip->setType(ChargeType::Single);
            $tip->setTitle(self::CHARGE_TITLE_TIP);
            $tip->setMoney($this->getChargeMoney($record['donate_amount'], self::CURRENCY));
            $tip->setTarget($tipjar->getAccounting());

            $checkout->addCharge($tip);
        }

        foreach ($checkout->getCharges() as $charge) {
            if (!\in_array($charge->getStatus(), [ChargeStatus::Charged, ChargeStatus::Refunded])) {
                continue;
            }

            $transaction = new Transaction();
            $transaction->setDateCreated($invested);
            $transaction->setMoney($charge->getMoney());
            $transaction->setOrigin($checkout->getOrigin());
            $transaction->setTarget($charge->getTarget());

            $charge->addTransaction($transaction);

            if ($charge->getStatus() !== ChargeStatus::Refunded) {
                continue;
            }

            $returned = new Transaction();
            $returned->setDateCreated(new \DateTime($record['returned']));
            $returned->setMoney($charge->getMoney());
            $returned->setOrigin($charge->getTarget());
            $returned->setTarget($checkout->getOrigin());

            $charge->addTransaction($returned);
        }

        $this->persist($checkout, $context);
    }

    private function getUser(array $record): ?User
    {
        return $this->userRepository->findOneBy(['migratedId' => $record['user']]);
    }

    private function getProject(array $record): ?Project
    {
        if (empty($record['project'])) {
            return null;
        }

        return $this->projectRepository->findOneBy(['migratedId' => $record['project']]);
    }

    private function getPlatformTipjar(): Tipjar
    {
        $tipjar = $this->tipjarRepository->findOneBy(['name' => self::PLATFORM_TIPJAR_NAME]);

        if ($tipjar) {
            return $tipjar;
        }

        $tipjar = new Tipjar();
        $tipjar->setName(self::PLATFORM_TIPJAR_NAME);

        $this->entityManager->persist($tipjar);
        $this->entityManager->flush();

        return $tipjar;
    }

    private function getChargeType(array $record): ChargeType
    {
        if (\in_array($record['method'], ['stripe_subscription'])) {
            return ChargeType::Recurring;
        }

        return ChargeType::Single;
    }

    private function getChargeMoney(int $amount, string $currency): Money
    {
        $amount = $amount * 100;

        if ($amount >= self::MAX_INT) {
            $amount = self::MAX_INT;
        }

        return new Money($amount, $currency);
    }

    private function getChargeStatus(array $record): ChargeStatus
    {
        switch ($record['status']) {
            case 0:
            case 1:
            case 3:
            case 7:
                if ($record['issue'] === 1) {
                    return ChargeStatus::InPending;
                }

                return ChargeStatus::Charged;
            case 2:
            case 4:
            case 6:
                return ChargeStatus::Refunded;
            default:
                return ChargeStatus::InPending;
        }
    }

    private function getCheckoutStatus(array $record): CheckoutStatus
    {
        switch ($record['status']) {
            case 0:
            case 1:
            case 3:
            case 7:
                if ($record['issue'] === 1) {
                    return CheckoutStatus::InPending;
                }

                return CheckoutStatus::Charged;
            default:
                return CheckoutStatus::InPending;
        }
    }

    private function getCheckoutGateway(array $record): string
    {
        switch ($record['method']) {
            case 'stripe_subscription':
                return StripeGateway::getName();
            case 'pool':
                return WalletGateway::getName();
            case 'paypal':
                return PaypalGateway::getName();
            case 'tpv':
                return CecaGateway::getName();
            case 'cash':
                return CashGateway::getName();
            case 'drop':
                return DropGateway::getName();
            default:
                return '';
        }
    }

    /**
     * @return Tracking[]
     */
    private function getCheckoutTrackings(array $record): array
    {
        $v3Tracking = new Tracking();
        $v3Tracking->setTitle(self::TRACKING_TITLE_V3);
        $v3Tracking->setValue($record['id']);

        $trackings = [$v3Tracking];

        if (!empty($record['payment'])) {
            $payment = new Tracking();
            $payment->setTitle(self::TRACKING_TITLE_PAYMENT);
            $payment->setValue($record['payment']);

            $trackings[] = $payment;
        }

        if (!empty($record['transaction'])) {
            $transaction = new Tracking();
            $transaction->setTitle(self::TRACKING_TITLE_TRANSACTION);
            $transaction->setValue($record['transaction']);

            $trackings[] = $transaction;
        }

        if (!empty($record['preapproval'])) {
            $preapproval = new Tracking();
            $preapproval->setTitle(self::TRACKING_TITLE_PREAPPROVAL);
            $preapproval->setValue($record['preapproval']);

            $trackings[] = $preapproval;
        }

        return $trackings;
    }
}
