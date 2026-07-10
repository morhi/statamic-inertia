<?php

namespace Morhi\StatamicInertia\Tests;

use Morhi\StatamicInertia\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
