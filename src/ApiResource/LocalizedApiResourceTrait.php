<?php

namespace App\ApiResource;

use ApiPlatform\Metadata as API;

trait LocalizedApiResourceTrait
{
    /**
     * List of the available content locales.
     *
     * @var array<string>
     */
    #[API\ApiProperty(writable: false)]
    public array $locales;
}
