<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Tests;

use Keeal\Checkout\HttpTransportInterface;
use Keeal\Checkout\KeealCheckout;
use Keeal\LaravelCheckout\Facades\Keeal;
use Keeal\LaravelCheckout\Facades\KeealPublic;
use Keeal\LaravelCheckout\KeealCheckoutServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [KeealCheckoutServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Keeal' => Keeal::class,
            'KeealPublic' => KeealPublic::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('keeal.api_key', 'keeal_sk_test_fixture');
        $app['config']->set('keeal.base_url', 'https://api.keeal.test/api');
        $app['config']->set('keeal.webhook_secret', 'whsec_test_fixture_secret');
        $app['config']->set('keeal.default_headers', []);
        $app['config']->set('keeal.mode', 'test');
    }

    protected function fakeKeealCheckoutHttp(HttpTransportInterface $transport): void
    {
        $this->app->instance(KeealCheckout::class, new KeealCheckout([
            'apiKey' => 'keeal_sk_test_fixture',
            'baseUrl' => 'https://api.keeal.test/api',
            'http' => $transport,
        ]));
    }
}
