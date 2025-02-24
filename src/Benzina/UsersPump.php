<?php

namespace App\Benzina;

use App\Entity\User\User;
use Goteo\Benzina\Pump\AbstractPump;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;

class UsersPump extends AbstractPump
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;
    use UsersPumpTrait;

    private int $userCount = 0;

    public function supports(mixed $sample): bool
    {
        if (\is_array($sample) && $this->hasAllKeys($sample, self::USER_KEYS)) {
            return true;
        }

        return false;
    }

    public function pump(mixed $record): void
    {
        $user = new User();
        $user->setHandle($this->buildHandle($record));
        $user->setPassword($record['password'] ?? '');
        $user->setEmail($record['email']);
        $user->setEmailConfirmed(false);
        $user->setName($record['name']);
        $user->setActive(false);
        $user->setMigrated(true);
        $user->setMigratedId($record['id']);
        $user->setDateCreated($this->getDateCreated($record));
        $user->setDateUpdated(new \DateTime());

        $this->persist($user);
        ++$this->userCount;
    }

    private function normalizeStringForHandle(string $value): ?string
    {
        // If email remove provider
        if (\str_contains($value, '@') && \preg_match('/^[\w]+[^@]/', $value, $matches)) {
            $value = $matches[0];
        }

        // Only lowercase a-z, numbers and underscore in user handles
        $value = \preg_replace('/[^a-z0-9_]/', '_', \strtolower($value));

        // Min length 4
        $value = \str_pad($value, 4, '_');

        // Max length 30
        $value = \substr($value, 0, 30);

        if (strlen(str_replace('_', '', $value)) < 1) {
            return null;
        }

        return $value;
    }

    private function buildHandle(array $record): string
    {
        $handle = $this->normalizeStringForHandle($record['id']);

        if ($handle === null) {
            $handle = $this->normalizeStringForHandle($record['email']);
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
