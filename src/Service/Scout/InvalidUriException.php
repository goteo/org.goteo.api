<?php

namespace App\Service\Scout;

class InvalidUriException extends \Exception
{
    protected $message = 'Could not validate the given value as an URL';
}
