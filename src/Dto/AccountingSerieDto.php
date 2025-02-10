<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use App\ApiResource\Accounting\AccountingApiResource;
use Symfony\Component\Validator\Constraints as Assert;

class AccountingSerieDto
{
    /**
     * The source Accounting for the data serie.
     */
    #[Assert\NotBlank()]
    public AccountingApiResource $accounting;

    /**
     * The start date for the data serie.
     */
    #[Assert\NotBlank()]
    public \DateTimeInterface $dateStart;

    /**
     * The end date for the data serie. Inclusive.
     */
    #[Assert\LessThanOrEqual('now')]
    #[ApiProperty(default: 'now')]
    public ?\DateTimeInterface $dateEnd = null;

    /**
     * The max number of data points in the serie.\
     * Returned data set might have less data points if there is not enough data.
     */
    #[Assert\Positive()]
    #[Assert\Range(min: 1, max: 100)]
    public int $maxLength = 10;

    public function __construct()
    {
        $this->dateEnd = $this->dateEnd ?: new \DateTimeImmutable('now');
    }
}
