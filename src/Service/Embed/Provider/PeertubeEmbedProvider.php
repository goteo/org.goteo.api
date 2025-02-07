<?php

namespace App\Service\Embed\Provider;

use Embera\Provider\ProviderAdapter as EmbedProvider;
use Embera\Provider\ProviderInterface as EmbedProviderInterface;
use Embera\Url;

/**
 * Generic PeerTube provider for Embera that can work for many instance hosts.
 */
class PeertubeEmbedProvider extends EmbedProvider implements EmbedProviderInterface
{
    protected $endpoint = '/services/oembed';

    protected static $hosts = [];

    /**
     * Is updated when Embera decides this provider works for the URL when validating it.
     */
    private ?string $currentHost = null;

    protected $httpsSupport = true;

    public function __construct($url, array $config = [])
    {
        parent::__construct($url, $config);

        self::$hosts = $config['peertube_hosts'];
    }

    public function validateUrl(Url $url): bool
    {
        foreach (self::$hosts as $host) {
            $hostPattern = \sprintf('~%s/w/([^/]+)~i', \preg_quote($host));

            if (!\preg_match($hostPattern, (string) $url)) {
                continue;
            }

            $this->currentHost = $host;

            return true;
        }

        return false;
    }

    public function getEndpoint(): string
    {
        return \sprintf('https://%s%s', $this->currentHost, $this->endpoint);
    }

    /** inline {@inheritdoc} */
    public function normalizeUrl(Url $url): Url
    {
        $url->convertToHttps();
        $url->removeQueryString();
        $url->removeLastSlash();

        return $url;
    }
}
