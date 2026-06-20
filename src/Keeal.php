<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout;

use Illuminate\Support\Facades\Config;

/**
 * Environment helpers (Stripe-style test vs live). Use this instead of scattering
 * config('keeal.mode') checks; only {@see self::MODE_TEST} enables test semantics.
 */
final class Keeal
{
    public const MODE_TEST = 'test';

    public const MODE_LIVE = 'live';

    /**
     * Normalized mode: {@see self::MODE_TEST} or {@see self::MODE_LIVE}.
     * Unknown values fall back to {@see self::MODE_LIVE} so production stays safe if env is mistyped.
     */
    public static function mode(): string
    {
        $raw = Config::get('keeal.mode', self::MODE_LIVE);

        return $raw === self::MODE_TEST ? self::MODE_TEST : self::MODE_LIVE;
    }

    public static function isTestMode(): bool
    {
        return self::mode() === self::MODE_TEST;
    }

    public static function isLiveMode(): bool
    {
        return self::mode() === self::MODE_LIVE;
    }
}
