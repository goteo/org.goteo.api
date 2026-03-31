<?php

namespace App\OAuth;

interface TokenOwnerInterface
{
    public function getEmail(): string;

    public function getFirstName(): string;

    public function getLastName(): string;
}
