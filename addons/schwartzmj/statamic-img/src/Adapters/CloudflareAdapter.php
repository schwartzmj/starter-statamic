<?php

namespace Schwartzmj\StatamicImg\Adapters;

use Schwartzmj\StatamicImg\Img;

class CloudflareAdapter implements IAdapter
{
    public static function toUrl(int $width_to_render, Img $img): string
    {
        $prefix = "/cdn-cgi/image/";
        return $prefix . "fit=scale-down,width={$width_to_render}" . $img->asset->url();
    }
}
