<?php

namespace App\Service\Embed\Filter;

class YoutubeEmbedFilter implements EmbedFilterInterface
{
    public static function supports(string $providerUrl): bool
    {
        return \str_ends_with($providerUrl, 'youtube.com');
    }

    public static function filter(array $data): array
    {
        if (!\array_key_exists('thumbnail_url', $data)) {
            return $data;
        }

        $data['thumbnail_url'] = \preg_replace('/\w+.jpg$/', 'maxresdefault.jpg', $data['thumbnail_url']);

        return $data;
    }
}
