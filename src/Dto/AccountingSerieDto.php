<?php

namespace App\Dto;

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
    #[Assert\NotBlank()]
    #[Assert\LessThanOrEqual('today')]
    public \DateTimeInterface $dateEnd;

        /**
     * The number of data points in the serie.
     */
    #[Assert\NotBlank()]
    #[Assert\Positive()]
    #[Assert\Range(min: 10, max: 100)]
    public int $length = 10;
}
