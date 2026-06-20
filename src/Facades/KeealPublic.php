<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Facades;

use Illuminate\Support\Facades\Facade;
use Keeal\Checkout\KeealCheckoutPublic;

/**
 * @method static array<string, mixed> retrieveSession(string $sessionId)
 * @method static array{paymentId: string, clientSecret: string|null} createPayment(string $sessionId, array<string, mixed> $params, array{idempotencyKey?: string|null} $options = [])
 * @method static void cancelSession(string $sessionId)
 * @method static void abandonSession(string $sessionId)
 * @method static array<string, mixed> paypalCreateOrder(string $sessionId, array<string, mixed> $params)
 * @method static array<string, mixed> paypalCapture(string $sessionId, array<string, mixed> $params)
 *
 * @see KeealCheckoutPublic
 */
final class KeealPublic extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return KeealCheckoutPublic::class;
    }
}
