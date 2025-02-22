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
        $user->setUsername($this->getUsername($record));
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

    private function normalizeUsername(string $username): ?string
    {
        // If email remove provider
        if (\str_contains($username, '@') && \preg_match('/^[\w]+[^@]/', $username, $matches)) {
            $username = $matches[0];
        }

        // Only lowercase a-z, numbers and underscore in usernames
        $username = \preg_replace('/[^a-z0-9_]/', '_', \strtolower($username));

        // Min length 4
        $username = \str_pad($username, 4, '_');

        // Max length 30
        $username = \substr($username, 0, 30);

        if (strlen(str_replace('_', '', $username)) < 1) {
            return null;
        }

        return $username;
    }

    private function getUsername(array $record): string
    {
        $username = $this->normalizeUsername($record['id']);

        if ($username === null) {
            $username = $this->normalizeUsername($record['email']);
        }

        $sequenceNumber = \substr($this->userCount, -2, 2);

        return \sprintf('%s_%s', $username, $sequenceNumber);
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
