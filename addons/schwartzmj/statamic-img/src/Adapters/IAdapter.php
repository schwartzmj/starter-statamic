<?php

namespace Schwartzmj\StatamicImg\Adapters;

use Schwartzmj\StatamicImg\Img;

interface IAdapter
{
    public static function toUrl(int $width_to_render, Img $img): string;
}
