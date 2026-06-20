<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Facades;

use Illuminate\Support\Facades\Facade;
use Keeal\Checkout\KeealCheckout;

/**
 * @method static array<string, mixed> createSession(array<string, mixed> $params, array{idempotencyKey?: string|null} $options = [])
 * @method static string createSessionUrl(array<string, mixed> $params, array{idempotencyKey?: string|null} $options = [])
 * @method static array<string, mixed> listMerchantSessions(array{limit?: int, page?: int}|null $options = null)
 * @method static array<string, mixed> retrieveMerchantSession(string $sessionId)
 * @method static array<string, mixed> retrieveSession(string $sessionId)
 * @method static array{paymentId: string, clientSecret: string|null} createPayment(string $sessionId, array<string, mixed> $params, array{idempotencyKey?: string|null} $options = [])
 * @method static void cancelSession(string $sessionId)
 * @method static void abandonSession(string $sessionId)
 * @method static array<string, mixed> paypalCreateOrder(string $sessionId, array<string, mixed> $params)
 * @method static array<string, mixed> paypalCapture(string $sessionId, array<string, mixed> $params)
 *
 * @see KeealCheckout
 */
final class Keeal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return KeealCheckout::class;
    }
}
