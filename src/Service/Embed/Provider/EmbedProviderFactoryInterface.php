<?php

namespace App\Service\Embed\Provider;

use Embera\Provider\ProviderInterface as EmbedProviderInterface;

interface EmbedProviderFactoryInterface
{
    /**
     * @return array Config for Embera
     */
    public function getConfig(): array;

    /**
     * @return array<string, EmbedProviderInterface>
     */
    public function createProviders(): array;
}
