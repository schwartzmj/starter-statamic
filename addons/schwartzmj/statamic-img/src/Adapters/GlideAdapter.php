<?php

namespace Schwartzmj\StatamicImg\Adapters;

use Schwartzmj\StatamicImg\Img;

class GlideAdapter implements IAdapter
{
    public static function toUrl(int $width_to_render, Img $img): string
    {
        $url = '';
        foreach (\Statamic::tag('glide:generate')->src($img->asset)->width($width_to_render) as $image) {
            $url = $image['url'];
        }
        return $url;
    }
}
