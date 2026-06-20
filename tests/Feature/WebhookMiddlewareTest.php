<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Keeal\LaravelCheckout\Http\Middleware\VerifyKeealWebhookSignature;
use Keeal\LaravelCheckout\Tests\TestCase;

final class WebhookMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/_test/keeal-webhook', fn () => response()->json(['ok' => true]))
            ->middleware(VerifyKeealWebhookSignature::class);
    }

    public function test_rejects_when_signature_header_missing(): void
    {
        $this->post('/_test/keeal-webhook', [], ['CONTENT_TYPE' => 'application/json'])
            ->assertStatus(401);
    }

    public function test_rejects_invalid_signature(): void
    {
        $raw = '{"type":"checkout.session.completed"}';
        $this->call(
            'POST',
            '/_test/keeal-webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_KEEAL_SIGNATURE' => 't=1,v1=deadbeef',
            ],
            $raw
        )->assertStatus(401);
    }

    public function test_accepts_valid_signature(): void
    {
        $raw = '{"type":"checkout.session.completed"}';
        $t = (string) time();
        $signedPayload = $t . '.' . $raw;
        $secret = 'whsec_test_fixture_secret';
        $v1 = hash_hmac('sha256', $signedPayload, $secret, false);

        $this->call(
            'POST',
            '/_test/keeal-webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_KEEAL_SIGNATURE' => 't=' . $t . ',v1=' . $v1,
            ],
            $raw
        )->assertOk()->assertJson(['ok' => true]);
    }

    public function test_rejects_when_webhook_secret_not_configured(): void
    {
        $this->app['config']->set('keeal.webhook_secret', '');

        Route::post('/_test/keeal-webhook-empty-secret', fn () => response()->noContent())
            ->middleware(VerifyKeealWebhookSignature::class);

        $this->post('/_test/keeal-webhook-empty-secret')->assertStatus(503);
    }
}
