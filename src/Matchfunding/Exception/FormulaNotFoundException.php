<?php

namespace App\Matchfunding\Exception;

class FormulaNotFoundException extends \Exception
{
    public const MISSING_NAME = "Could not find a Formula by the name '%s', value does not exist";

    public function __construct(
        string $name,
        string $message = self::MISSING_NAME,
        ...$params,
    ) {
        parent::__construct(\sprintf(
            $message,
            ...[$name, ...$params]
        ));
    }
}
