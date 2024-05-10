<?php

namespace Schwartzmj\StatamicImg;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        \Schwartzmj\StatamicImg\Tags\ImgTag::class,
    ];

    protected $modifiers = [
        //
    ];

    protected $fieldtypes = [
        //
    ];

    protected $widgets = [
        //
    ];

    protected $commands = [
        //
    ];

    public function bootAddon()
    {
        //
    }
}
