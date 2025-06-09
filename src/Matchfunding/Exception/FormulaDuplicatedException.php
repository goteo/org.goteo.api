<?php

namespace App\Matchfunding\Exception;

class FormulaDuplicatedException extends \Exception
{
    public const DUPLICATED_NAME = "Duplicate Formula name '%s' by class '%s', value already in use by class '%s'";

    public function __construct(
        string $duplicatedName,
        string $duplicatedClass,
        string $formulaClass,
        string $message = self::DUPLICATED_NAME,
    ) {
        parent::__construct(\sprintf(
            $message,
            $duplicatedName,
            $duplicatedClass,
            $formulaClass
        ));
    }
}
