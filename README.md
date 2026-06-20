# keeal/laravel-checkout

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](./LICENSE)

Official **Laravel** integration for Keeal **hosted checkout**. Wraps [`keeal/keeal-php`](https://github.com/KeealPay/keeal-php) with a ServiceProvider, Facade, published config, and webhook middleware.

**Repository:** [github.com/KeealPay/keeal-laravel](https://github.com/KeealPay/keeal-laravel) · **Package:** `keeal/laravel-checkout`

---

## Overview

Keeal **hosted checkout** is a redirect-based payment flow:

1. **Create a session** on your server with your secret API key (`keeal_sk_…`).
2. **Redirect** the customer to the session `url` returned by the API.
3. **Fulfill** your order when you receive a signed `checkout.session.completed` webhook.

Your server never handles card data. Payment UI, PayPal, and cancellation are handled on Keeal's hosted page.

This package auto-registers `KeealCheckoutServiceProvider`, binds `Keeal\Checkout\KeealCheckout` in the container, and provides Laravel-friendly webhook verification.

---

## Installation

```bash
composer require keeal/laravel-checkout
```

This package depends on [`keeal/keeal-php`](https://github.com/KeealPay/keeal-php) (^0.2).

Requires **PHP 8.2+** and **Laravel 11 or 12**.

Publish configuration:

```bash
php artisan vendor:publish --tag=keeal-config
```

---

## Configuration

Add these variables to your `.env`:

| Variable | Description |
|----------|-------------|
| `KEEAL_API_KEY` | Secret API key (`keeal_sk_…`) from the Keeal dashboard |
| `KEEAL_BASE_URL` | API base URL including `/api`, e.g. `https://api.keeal.com/api` |
| `KEEAL_WEBHOOK_SECRET` | Webhook signing secret (`whsec_…`) from **Settings → API Keys → Webhook** |

Published config file: `config/keeal.php` maps these to `keeal.api_key`, `keeal.base_url`, and `keeal.webhook_secret`.

Use separate keys, base URLs, and webhook secrets for staging and production.

---

## Quick start

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Keeal\LaravelCheckout\Facades\Keeal;
use Keeal\LaravelCheckout\WebhookSignature;

class CheckoutController extends Controller
{
    public function start(Request $request)
    {
        $session = Keeal::createSession([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Pro plan'],
                    'unit_amount' => 2900,
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('checkout.success'),
            'cancel_url' => route('checkout.cancel'),
            'client_reference_id' => (string) $request->user()->id,
        ]);

        return redirect()->away($session['url']);
    }

    public function webhook(Request $request)
    {
        $event = WebhookSignature::constructEvent(
            $request->getContent(),
            (string) $request->header('X-Keeal-Signature', ''),
        );

        if ($event['type'] === 'checkout.session.completed') {
            // Fulfill order using $event['data']['object']
        }

        return response('ok');
    }
}
```

Register routes:

```php
use App\Http\Controllers\CheckoutController;

Route::post('/checkout', [CheckoutController::class, 'start']);
Route::post('/webhooks/keeal', [CheckoutController::class, 'webhook'])
    ->middleware('keeal.webhook');
```

### Dependency injection

```php
use Keeal\Checkout\KeealCheckout;

public function __construct(private KeealCheckout $checkout) {}

$session = $this->checkout->createSession([/* ... */]);
```

---

## API reference

### `Keeal` facade

Delegates to `Keeal\Checkout\KeealCheckout` (server-side, requires API key).

| Method | Signature | Description | Status |
|--------|-----------|-------------|--------|
| `createSession` | `createSession(array $params, array $options = [])` → `array` | Create a checkout session. Returns `{ id, url }`. | Hosted |
| `createSessionUrl` | `createSessionUrl(array $params, array $options = [])` → `string` | Returns the hosted checkout `url`. | Hosted |
| `listMerchantSessions` | `listMerchantSessions(?array $options = null)` → `array` | List your checkout sessions. Options: `limit`, `page`. | Hosted |
| `retrieveMerchantSession` | `retrieveMerchantSession(string $sessionId)` → `array` | Get one session by `cs_…` id, including `payments[]`. | Hosted |
| `retrieveSession` | `retrieveSession(string $sessionId)` → `array` | Public session lookup (no API key sent). | Hosted |
| `createPayment` | `createPayment(string $sessionId, array $params, array $options = [])` → `array` | Legacy custom `/pay` flow. | **Deprecated** |
| `cancelSession` | `cancelSession(string $sessionId)` → `void` | Legacy session cancel. | **Deprecated** |
| `abandonSession` | `abandonSession(string $sessionId)` → `void` | Legacy session abandon. | **Deprecated** |
| `paypalCreateOrder` | `paypalCreateOrder(string $sessionId, array $params)` → `array` | Legacy PayPal create-order. | **Deprecated** |
| `paypalCapture` | `paypalCapture(string $sessionId, array $params)` → `array` | Legacy PayPal capture. | **Deprecated** |

### `KeealPublic` facade

Delegates to `Keeal\Checkout\KeealCheckoutPublic`. **Not recommended for new integrations.**

| Method | Signature | Description | Status |
|--------|-----------|-------------|--------|
| `retrieveSession` | `retrieveSession(string $sessionId)` → `array` | Public session lookup. | **Deprecated** |
| `createPayment` | `createPayment(string $sessionId, array $params, array $options = [])` → `array` | Legacy `/pay` from a custom UI. | **Deprecated** |
| `cancelSession` | `cancelSession(string $sessionId)` → `void` | Legacy cancel. | **Deprecated** |
| `abandonSession` | `abandonSession(string $sessionId)` → `void` | Legacy abandon. | **Deprecated** |
| `paypalCreateOrder` | `paypalCreateOrder(string $sessionId, array $params)` → `array` | Legacy PayPal. | **Deprecated** |
| `paypalCapture` | `paypalCapture(string $sessionId, array $params)` → `array` | Legacy PayPal. | **Deprecated** |

### `WebhookSignature`

| Method | Signature | Description |
|--------|-----------|-------------|
| `verify` | `verify(string $rawBody, string $signatureHeader, ?string $whsec = null, int $toleranceSeconds = 300)` → `bool` | Verify `X-Keeal-Signature`. Uses `config('keeal.webhook_secret')` when `$whsec` is null. |
| `constructEvent` | `constructEvent(string $rawBody, string $signatureHeader, ?string $whsec = null, int $toleranceSeconds = 300)` → `array` | Verify signature and decode the JSON envelope. |

### `keeal.webhook` middleware

`Keeal\LaravelCheckout\Http\Middleware\VerifyKeealWebhookSignature`

| Behavior | Description |
|----------|-------------|
| Validates config | Returns `503` if `keeal.webhook_secret` is not set. |
| Validates header | Returns `401` if `X-Keeal-Signature` is missing or invalid. |
| Passes through | Calls `$next($request)` when signature is valid. |

Apply to webhook routes so handlers can trust the request body was verified:

```php
Route::post('/webhooks/keeal', [WebhookController::class, 'handle'])
    ->middleware('keeal.webhook');
```

### Service provider

`Keeal\LaravelCheckout\KeealCheckoutServiceProvider` is auto-discovered via Laravel package discovery.

| Binding | Resolves to |
|---------|-------------|
| `Keeal\Checkout\KeealCheckout` | Singleton configured from `config/keeal.php` |
| `Keeal\Checkout\KeealCheckoutPublic` | Singleton using `keeal.base_url` |

---

## Webhook verification

Configure your webhook URL in the Keeal dashboard. Always verify on the **raw** request body.

**With middleware** (recommended):

```php
Route::post('/webhooks/keeal', function (Request $request) {
    $event = json_decode($request->getContent(), true);
    // Signature already verified by keeal.webhook middleware
    return response('ok');
})->middleware('keeal.webhook');
```

**Manual verification** (e.g. queued jobs):

```php
use Keeal\LaravelCheckout\WebhookSignature;

$event = WebhookSignature::constructEvent($rawBody, $signatureHeader);
```

Signature format: `t=<unix_seconds>,v1=<hex_hmac>` where HMAC-SHA256 is computed over `<t>.<rawBody>` using your `whsec_…` secret.

---

## Subscription checkout

Hosted subscription checkout follows the same pattern as Stripe Checkout for subscriptions:

1. Create a **product** and **price** in the Keeal dashboard and copy the price id (`price_…`).
2. Create a session with `mode => 'subscription'` and the catalog price id.
3. Redirect to the session `url`.
4. Handle `checkout.session.completed` and subscription webhooks.

```php
// Stripe-style line_items (recommended)
Keeal::createSession([
    'mode' => 'subscription',
    'line_items' => [['price' => 'price_abc123', 'quantity' => 1]],
    'success_url' => route('subscription.welcome'),
    'cancel_url' => route('pricing'),
]);

// Or subscription_data (line_items expanded server-side)
Keeal::createSession([
    'mode' => 'subscription',
    'subscription_data' => ['price_id' => 'price_abc123'],
    'success_url' => route('subscription.welcome'),
    'cancel_url' => route('pricing'),
]);
```

Subscription lifecycle events (`subscription.created`, `subscription.activated`, etc.) are delivered to the same webhook URL and verified with the same signing secret as checkout events.

---

## Legacy & deprecated APIs

These remain available for backward compatibility but are **not offered to new merchants**:

| Symbol | Use instead |
|--------|-------------|
| `KeealPublic` facade | `Keeal::createSession()` + redirect to `url` |
| `Keeal::createPayment()` | Redirect to session `url` |
| `KeealPublic::createPayment()` | Redirect to session `url` |
| `cancelSession` / `abandonSession` | Handled on Keeal's hosted pay page |
| `paypalCreateOrder` / `paypalCapture` | Handled on Keeal's hosted pay page |

---

## Development

**Standalone clone:**

```bash
composer install
composer test
```

**Monorepo** (alongside `keeal-sdk/php`): add a path repository to your root `composer.json` so `keeal/keeal-php` resolves locally:

```json
{
  "repositories": [
    { "type": "path", "url": "../php", "options": { "symlink": true } }
  ]
}
```

---

## License

**MIT** — see [`LICENSE`](./LICENSE).
