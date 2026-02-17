<?php

namespace App\Gateway;

use App\Library\Link;

class GatewayLink extends Link
{
    /**
     * The HTTP method required to make the related call.
     */
    public string $method;

    /**
     * The type of the link indicates who is the intended user of a link.\
     * `debug` links are for developers and platform maintainers to get useful information about the checkout.\
     * `payment` links are for end-users who must visit this link to complete the checkout.
     */
    public GatewayLinkType $type;

    public static function tryFrom($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        $link = new self();
        $link->url = $value['url'];
        $link->rel = $value['rel'];
        $link->method = $value['method'];
        $link->type = GatewayLinkType::tryFrom($value['type']);

        return $link;
    }
}
