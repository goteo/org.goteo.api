<?php

namespace App\Library\Benzina\Pump;

use App\Entity\Accounting;
use App\Entity\AccountingTransaction;
use App\Entity\GatewayCharge;
use App\Entity\GatewayChargeType;
use App\Entity\GatewayCheckout;
use App\Entity\GatewayCheckoutStatus;
use App\Entity\GatewayTracking;
use App\Entity\Money;
use App\Entity\Tipjar;
use App\Library\Benzina\Pump\Trait\ArrayPumpTrait;
use App\Library\Benzina\Pump\Trait\ProgressivePumpTrait;
use App\Library\Economy\Payment\CashGateway;
use App\Library\Economy\Payment\CecaGateway;
use App\Library\Economy\Payment\DropGateway;
use App\Library\Economy\Payment\PaypalGateway;
use App\Library\Economy\Payment\StripeGateway;
use App\Library\Economy\Payment\WalletGateway;
use App\Repository\ProjectRepository;
use App\Repository\TipjarRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class CheckoutsPump extends AbstractPump implements PumpInterface
{
    use ArrayPumpTrait;
    use ProgressivePumpTrait;
    public const TRACKING_TITLE_V3 = 'v3 Invest ID';
    public const TRACKING_TITLE_PAYMENT = 'v3 Invest Payment';
    public const TRACKING_TITLE_TRANSACTION = 'v3 Invest Transaction';
    public const TRACKING_TITLE_PREAPPROVAL = 'v3 Invest Preapproval';

    private const PLATFORM_TIPJAR_NAME = 'platform';

    private const MAX_INT = 2147483647;

    private const INVEST_KEYS = [
        'id',
        'user',
        'project',
        'account',
        'amount',
        'amount_original',
        'currency',
        'currency_rate',
        'donate_amount',
        'status',
        'anonymous',
        'resign',
        'invested',
        'charged',
        'returned',
        'preapproval',
        'payment',
        'transaction',
        'method',
        'admin',
        'campaign',
        'datetime',
        'drops',
        'droped',
        'call',
        'matcher',
        'issue',
        'pool',
        'extra_info',
    ];

    public function __construct(
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
        private TipjarRepository $tipjarRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function supports(mixed $batch): bool
    {
        if (!\is_array($batch) || !\array_key_exists(0, $batch)) {
            return false;
        }

        return $this->hasAllKeys($batch[0], self::INVEST_KEYS);
    }

    public function pump(mixed $batch): void
    {
        $users = $this->getUsers($batch);
        $projects = $this->getProjects($batch);

        $tipjar = $this->getPlatformTipjar();

        $pumped = $this->getPumped(GatewayCheckout::class, $batch, ['migratedReference' => 'id']);

        foreach ($batch as $key => $record) {
            if ($this->isPumped($record, $pumped, ['migratedReference' => 'id'])) {
                continue;
            }

            if (!\array_key_exists($record['project'], $projects)) {
                continue;
            }

            if (!$record['amount'] || $record['amount'] < 1) {
                continue;
            }

            if (!$record['method'] || empty($record['method'])) {
                continue;
            }

            $user = $users[$record['user']];
            $project = $projects[$record['project']];

            $checkout = new GatewayCheckout();
            $checkout->setOrigin($user->getAccounting());
            $checkout->setStatus($this->getCheckoutStatus($record));
            $checkout->setGateway($this->getCheckoutGateway($record));

            foreach ($this->getCheckoutTrackings($record) as $tracking) {
                $checkout->addGatewayTracking($tracking);
            }

            $checkout->setMigrated(true);
            $checkout->setMigratedReference($record['id']);
            $checkout->setMetadata([
                'payment' => $record['payment'],
                'transaction' => $record['transaction'],
                'preapproval' => $record['preapproval'],
            ]);

            $checkout->setDateCreated(new \DateTime($record['invested']));

            $charge = new GatewayCharge();
            $charge->setType($this->getChargeType($record));
            $charge->setMoney($this->getChargeMoney($record['amount'], $record['currency']));
            $charge->setTarget($project->getAccounting());

            if ($record['donate_amount'] > 0) {
                $tip = new GatewayCharge();
                $tip->setType(GatewayChargeType::Single);
                $tip->setMoney($this->getChargeMoney($record['donate_amount'], $record['currency']));
                $tip->setTarget($tipjar->getAccounting());

                $this->entityManager->persist($tip);
                $checkout->addCharge($tip);
            }

            $this->entityManager->persist($charge);
            $checkout->addCharge($charge);

            if ($checkout->getStatus() === GatewayCheckoutStatus::Charged) {
                foreach ($checkout->getCharges() as $charge) {
                    $transaction = new AccountingTransaction();
                    $transaction->setMoney($charge->getMoney());
                    $transaction->setOrigin($checkout->getOrigin());
                    $transaction->setTarget($charge->getTarget());

                    $charge->setTransaction($transaction);

                    $this->entityManager->persist($transaction);
                    $this->entityManager->persist($charge);
                }
            }

            $this->entityManager->persist($checkout);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function getUsers(array $batch): array
    {
        $users = $this->userRepository->findBy(['migratedReference' => \array_map(function ($batch) {
            return $batch['user'];
        }, $batch)]);

        $usersByMigratedReference = [];
        foreach ($users as $user) {
            $usersByMigratedReference[$user->getMigratedReference()] = $user;
        }

        return $usersByMigratedReference;
    }

    private function getProjects(array $batch): array
    {
        $projects = $this->projectRepository->findBy(['migratedReference' => \array_map(function ($batch) {
            return $batch['project'];
        }, $batch)]);

        $projectsByMigratedReference = [];
        foreach ($projects as $project) {
            $projectsByMigratedReference[$project->getMigratedReference()] = $project;
        }

        return $projectsByMigratedReference;
    }

    private function getPlatformTipjar(): Tipjar
    {
        $tipjar = $this->tipjarRepository->findOneBy(['name' => self::PLATFORM_TIPJAR_NAME]);

        if ($tipjar) {
            return $tipjar;
        }

        $tipjar = new Tipjar();
        $tipjar->setName(self::PLATFORM_TIPJAR_NAME);

        $accounting = new Accounting();
        $accounting->setTipjar($tipjar);

        $this->entityManager->persist($tipjar);
        $this->entityManager->persist($accounting);
        $this->entityManager->flush();

        return $tipjar;
    }

    private function getChargeType(array $record): GatewayChargeType
    {
        if (\in_array($record['method'], ['stripe_subscription'])) {
            return GatewayChargeType::Recurring;
        }

        return GatewayChargeType::Single;
    }

    private function getChargeMoney(int $amount, string $currency): Money
    {
        $amount = $amount * 100;

        if ($amount >= self::MAX_INT) {
            $amount = self::MAX_INT;
        }

        return new Money($amount, $currency);
    }

    private function getCheckoutStatus(array $record): GatewayCheckoutStatus
    {
        if ($record['status'] < 1) {
            return GatewayCheckoutStatus::Pending;
        }

        return GatewayCheckoutStatus::Charged;
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
     * @return GatewayTracking[]
     */
    private function getCheckoutTrackings(array $record): array
    {
        $v3Tracking = new GatewayTracking();
        $v3Tracking->setValue($record['id']);
        $v3Tracking->setTitle(self::TRACKING_TITLE_V3);

        $trackings = [$v3Tracking];

        if (!empty($record['payment'])) {
            $payment = new GatewayTracking();
            $payment->setValue($record['payment']);
            $payment->setTitle(self::TRACKING_TITLE_PAYMENT);

            $trackings[] = $payment;
        }

        if (!empty($record['transaction'])) {
            $transaction = new GatewayTracking();
            $transaction->setValue($record['transaction']);
            $transaction->setTitle(self::TRACKING_TITLE_TRANSACTION);

            $trackings[] = $transaction;
        }

        if (!empty($record['preapproval'])) {
            $preapproval = new GatewayTracking();
            $preapproval->setValue($record['preapproval']);
            $preapproval->setTitle(self::TRACKING_TITLE_PREAPPROVAL);

            $trackings[] = $preapproval;
        }

        return $trackings;
    }
}
