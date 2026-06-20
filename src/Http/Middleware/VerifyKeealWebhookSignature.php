<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Keeal\Checkout\WebhookVerifier;
use Keeal\LaravelCheckout\WebhookSignature;
use Symfony\Component\HttpFoundation\Response;

final class VerifyKeealWebhookSignature
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ((string) config('keeal.webhook_secret', '') === '') {
            abort(503, 'Keeal webhook signing secret is not configured.');
        }

        $signature = $request->header(WebhookVerifier::SIGNATURE_HEADER);
        if (! is_string($signature) || $signature === '') {
            abort(401, 'Missing X-Keeal-Signature header.');
        }

        $raw = $request->getContent();
        if ($raw === '') {
            abort(400, 'Empty webhook body.');
        }

        if (! WebhookSignature::verify($raw, $signature)) {
            abort(401, 'Invalid Keeal webhook signature.');
        }

        return $next($request);
    }
}
