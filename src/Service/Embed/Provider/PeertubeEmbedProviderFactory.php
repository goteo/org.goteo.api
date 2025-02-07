<?php

namespace App\Service\Embed\Provider;

use Embera\Provider\ProviderInterface as EmbedProviderInterface;

class PeertubeEmbedProviderFactory implements EmbedProviderFactoryInterface
{
    /**
     * @param string[] $hosts
     */
    public function __construct(
        private array $hosts,
    ) {}

    public function getConfig(): array
    {
        return ['peertube_hosts' => $this->hosts];
    }

    /**
     * @return array<string, EmbedProviderInterface>
     */
    public function createProviders(): array
    {
        $providers = [];

        foreach ($this->hosts as $host) {
            $providers[$host] = PeertubeEmbedProvider::class;
        }

        return $providers;
    }
}
