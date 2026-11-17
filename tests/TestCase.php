<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Theaarch\Oceanpay\OceanpayServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            OceanpayServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
    }
}
