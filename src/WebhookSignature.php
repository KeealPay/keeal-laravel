<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout;

use Illuminate\Support\Facades\Config;
use Keeal\Checkout\WebhookVerifier;

/**
 * Verifies Keeal webhook `X-Keeal-Signature` using {@see WebhookVerifier}.
 * Pass an explicit secret when verifying outside HTTP middleware (e.g. queued jobs).
 */
final class WebhookSignature
{
    /**
     * @param  string  $rawBody  Raw request body (do not re-encode JSON).
     * @param  string  $signatureHeader  Value of `X-Keeal-Signature` (`t=…,v1=…`).
     * @param  string|null  $whsec  Defaults to `config('keeal.webhook_secret')` when null.
     */
    public static function verify(string $rawBody, string $signatureHeader, ?string $whsec = null, int $toleranceSeconds = 300): bool
    {
        $secret = $whsec ?? (string) Config::get('keeal.webhook_secret', '');
        if ($secret === '') {
            return false;
        }

        return WebhookVerifier::verify($rawBody, $signatureHeader, $secret, $toleranceSeconds);
    }

    /**
     * Verify signature and decode the webhook JSON envelope.
     *
     * @return array<string, mixed>
     */
    public static function constructEvent(
        string $rawBody,
        string $signatureHeader,
        ?string $whsec = null,
        int $toleranceSeconds = 300,
    ): array {
        $secret = $whsec ?? (string) Config::get('keeal.webhook_secret', '');
        if ($secret === '') {
            throw new \InvalidArgumentException('Keeal webhook signing secret is not configured.');
        }

        return WebhookVerifier::constructEvent($rawBody, $signatureHeader, $secret, $toleranceSeconds);
    }
}
