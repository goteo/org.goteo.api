<?php

namespace App\Entity\Accounting;

interface AccountingOwnerInterface
{
    public function getId(): ?int;

    public function getAccounting(): ?Accounting;

    public function setAccounting(Accounting $accounting): static;
}
