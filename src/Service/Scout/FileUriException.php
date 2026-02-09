<?php

namespace App\Service\Scout;

use Psr\Http\Message\UriInterface;

class FileUriException extends \Exception
{
    private UriInterface $uri;

    public function __construct(
        UriInterface $uri,
        string $message = 'Cannot scout URLs to files (%s)',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(\sprintf($message, $uri), $code, $previous);

        $this->uri = $uri;
    }

    /**
     * The URI to the file.
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }
}
