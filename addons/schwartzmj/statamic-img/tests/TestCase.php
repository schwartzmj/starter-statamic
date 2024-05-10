<?php

namespace Schwartzmj\StatamicImg\Tests;

use Schwartzmj\StatamicImg\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
