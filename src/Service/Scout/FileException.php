<?php

namespace App\Service\Scout;

class FileException extends \Exception
{
    protected $message = 'Cannot scout URLs to files';
}
