<?php

namespace App\Service\Embed;

interface EmbedFilterInterface
{
    /**
     * Check for support of the data provider.
     *
     * @param string $providerUrl The URL of the provider, as given by the Embera Provider
     */
    public static function supports(string $providerUrl): bool;

    /**
     * Apply filter to embed data.
     *
     * @param array $data The embed data, as given by the Embera Provider
     *
     * @return array The transformed data array
     */
    public static function filter(array $data): array;
}
