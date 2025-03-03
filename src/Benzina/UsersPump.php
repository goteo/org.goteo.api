<?php

namespace App\Benzina;

use App\Entity\User\User;
use App\Service\UserService;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;
use Goteo\Benzina\Pump\PumpInterface;

class UsersPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;
    use UsersPumpTrait;

    private int $userCount = 0;

    public function supports(mixed $sample): bool
    {
        if ($this->hasAllKeys($sample, self::USER_KEYS)) {
            return true;
        }

        return false;
    }

    public function pump(mixed $record, array $context): void
    {
        $user = new User();
        $user->setHandle($this->buildHandle($record));
        $user->setPassword($record['password'] ?? '');
        $user->setEmail($record['email']);
        $user->setEmailConfirmed(false);
        $user->setActive(false);
        $user->setMigrated(true);
        $user->setMigratedId($record['id']);
        $user->setDateCreated($this->getDateCreated($record));
        $user->setDateUpdated(new \DateTime());

        $this->persist($user, $context);
        ++$this->userCount;
    }

    private function buildHandle(array $record): string
    {
        try {
            $handle = UserService::asHandle($record['id']);
        } catch (\Exception $e) {
            $handle = UserService::asHandle($record['email']);
        }

        $sequenceNumber = \substr($this->userCount, -2, 2);

        return \sprintf('%s_%s', $handle, $sequenceNumber);
    }

    private function getDateCreated(array $record): \DateTime
    {
        $created = new \DateTime($record['created']);

        if ($created > new \DateTime('2011-01-01')) {
            return $created;
        }

        return new \DateTime($record['modified']);
    }
}
