<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Tests\Unit;

use Keeal\LaravelCheckout\Keeal;
use Keeal\LaravelCheckout\Tests\TestCase;

final class KeealModeTest extends TestCase
{
    public function test_defaults_to_live_when_unset(): void
    {
        $this->app['config']->set('keeal.mode', null);
        self::assertSame(Keeal::MODE_LIVE, Keeal::mode());
        self::assertTrue(Keeal::isLiveMode());
        self::assertFalse(Keeal::isTestMode());
    }

    public function test_test_mode(): void
    {
        $this->app['config']->set('keeal.mode', Keeal::MODE_TEST);
        self::assertTrue(Keeal::isTestMode());
        self::assertFalse(Keeal::isLiveMode());
    }

    public function test_invalid_values_map_to_live(): void
    {
        $this->app['config']->set('keeal.mode', 'staging');
        self::assertSame(Keeal::MODE_LIVE, Keeal::mode());
        self::assertTrue(Keeal::isLiveMode());
    }
}
