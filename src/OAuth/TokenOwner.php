<?php

namespace App\OAuth;

class TokenOwner implements TokenOwnerInterface
{
    public function __construct(
        private string $email,
        private string $firstName,
        private string $lastName,
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }
}
