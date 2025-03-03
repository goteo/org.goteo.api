<?php

namespace App\Service\Embed\Filter;

class VimeoEmbedFilter implements EmbedFilterInterface
{
    public static function supports(string $providerUrl): bool
    {
        return \str_ends_with($providerUrl, 'vimeo.com');
    }

    public static function filter(array $data): array
    {
        if (!\array_key_exists('thumbnail_url', $data)) {
            return $data;
        }

        $data['thumbnail_url'] = \preg_replace('/_\d+x\d+$/', '_900x500', $data['thumbnail_url']);

        return $data;
    }
}
