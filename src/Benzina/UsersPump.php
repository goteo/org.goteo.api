<?php

namespace App\Benzina;

use App\Entity\User\Organization;
use App\Entity\User\Person;
use App\Entity\User\User;
use App\Entity\User\UserType;
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
        $user->setType($this->getUserType($record));

        match ($user->getType()) {
            UserType::Individual => $user = $this->setUserPerson($record, $user),
            UserType::Organization => $user = $this->setUserOrganization($record, $user),
        };

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

        return \sprintf('%s_%02d', $handle, $this->userCount % 100);
    }

    private function getDateCreated(array $record): \DateTime
    {
        $created = new \DateTime($record['created'] ?? '0000-00-00');

        if ($created > new \DateTime('2011-01-01')) {
            return $created;
        }

        return new \DateTime($record['modified'] ?? 'now');
    }

    private function getUserType(array $record): UserType
    {
        switch ($record['legal_entity']) {
            case 0:
            case 1:
                return UserType::Individual;
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                return UserType::Organization;
            default:
                return UserType::Individual;
        }
    }

    private function setUserPerson(array $record, User $user): User
    {
        $namePieces = \explode(' ', $record['name']);
        $namePiecesCount = \count($namePieces);

        $firstName = $record['name'];
        $lastName = '';

        if ($namePiecesCount === 2) {
            [$firstName, $lastName] = $namePieces;
        }

        if ($namePiecesCount === 3) {
            $firstName = $namePieces[0];
            $lastName = \join(' ', \array_slice($namePieces, 1));
        }

        if ($namePiecesCount > 3) {
            $firstName = \join(' ', \array_slice($namePieces, 0, 2));
            $lastName = \join(' ', \array_slice($namePieces, 2));
        }

        $person = new Person();
        $person->setFirstName($firstName);
        $person->setLastName($lastName);

        $user->setPerson($person);

        return $user;
    }

    private function setUserOrganization(array $record, User $user): User
    {
        $org = new Organization();
        $org->setBusinessName($record['name']);

        $user->setOrganization($org);

        return $user;
    }
}
