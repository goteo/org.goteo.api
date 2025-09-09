<?php

namespace App\Benzina;

use App\Entity\Gateway\Charge;
use App\Entity\Project\Reward;
use App\Entity\Project\RewardClaim;
use App\Entity\User\User;
use App\Gateway\ChargeStatus;
use App\Money\MoneyService;
use App\Repository\Gateway\ChargeRepository;
use App\Repository\Project\RewardRepository;
use App\Repository\User\UserRepository;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;
use Goteo\Benzina\Pump\PumpInterface;

class InvestRewardsPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DatabasePumpTrait;
    use DoctrinePumpTrait;
    use InvestsPumpTrait;

    /** @var array<string, int> */
    private array $userCache = [];

    /** @var array<string, int> */
    private array $chargeCache = [];

    /** @var array<string, int> */
    private array $rewardCache = [];

    public function __construct(
        private RewardRepository $rewardRepository,
        private UserRepository $userRepository,
        private ChargeRepository $chargeRepository,
        private MoneyService $moneyService,
    ) {}

    public function supports(mixed $sample): bool
    {
        if ($this->hasAllKeys($sample, ['invest', 'reward', 'fulfilled'])) {
            return true;
        }

        return false;
    }

    public function pump(mixed $record, array $context): void
    {
        $reward = $this->getReward($record);
        if ($reward === null) {
            return;
        }

        $invest = $this->getInvest($record, $context);
        if ($invest === null) {
            return;
        }

        $charge = $this->getCharge($invest);
        if ($charge === null) {
            return;
        }

        $user = $this->getUser($invest);
        if ($user === null) {
            return;
        }

        if ($charge->getStatus() !== ChargeStatus::Charged) {
            return;
        }

        $claim = new RewardClaim();
        $claim->setOwner($user);
        $claim->setCharge($charge);

        $reward->addClaim($claim);
        $claim->setReward($reward);

        $this->persist($claim, $context);
    }

    private function getInvest(array $record, array $context): ?array
    {
        $query = $this->getDbConnection($context)->prepare(
            'SELECT * FROM `invest` i WHERE i.id = :invest'
        );

        $query->execute(['invest' => $record['invest']]);

        $result = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        return $result;
    }

    private function getReward(array $record): ?Reward
    {
        $id = $record['reward'];

        if (isset($this->rewardCache[$id])) {
            return $this->rewardRepository->find($this->rewardCache[$id]);
        }

        $reward = $this->rewardRepository->findOneBy(['migratedId' => $id]);
        if (!$reward) {
            return null;
        }

        $this->rewardCache[$id] = $reward->getId();

        return $reward;
    }

    private function getCharge(array $invest): ?Charge
    {
        $id = $invest['id'];

        if (isset($this->chargeCache[$id])) {
            return $this->chargeRepository->find($this->chargeCache[$id]);
        }

        $charge = $this->chargeRepository->findOneBy(['migratedId' => $id]);
        if (!$charge) {
            return null;
        }

        $this->chargeCache[$id] = $charge->getId();

        return $charge;
    }

    private function getUser(array $invest): ?User
    {
        $id = $invest['user'];

        if (isset($this->userCache[$id])) {
            return $this->userRepository->find($this->userCache[$id]);
        }

        $user = $this->userRepository->findOneBy(['migratedId' => $id]);

        $this->userCache[$id] = $user->getId();

        return $user;
    }
}
