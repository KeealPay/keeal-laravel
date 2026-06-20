<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Tests\Feature;

use Keeal\Checkout\KeealCheckout;
use Keeal\LaravelCheckout\Tests\Fakes\FakeHttpTransport;
use Keeal\LaravelCheckout\Tests\TestCase;

final class KeealCheckoutClientTest extends TestCase
{
    public function test_resolved_client_can_create_session_via_fake_transport(): void
    {
        $fake = new FakeHttpTransport([
            ['status' => 200, 'body' => '{"id":"cs_fixture","url":"https://pay.keeal.test/cs_fixture"}'],
        ]);

        $this->fakeKeealCheckoutHttp($fake);

        $client = $this->app->make(KeealCheckout::class);

        $result = $client->createSession([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => 'Test'],
                        'unit_amount' => 1000,
                    ],
                    'quantity' => 1,
                ],
            ],
        ]);

        self::assertSame('cs_fixture', $result['id']);
        self::assertSame('https://pay.keeal.test/cs_fixture', $result['url']);
        self::assertCount(1, $fake->requests);
        self::assertSame('POST', $fake->requests[0]['method']);
        self::assertStringContainsString('/checkout/sessions', $fake->requests[0]['url']);
        $headers = $fake->requests[0]['headers'];
        $joined = implode("\n", $headers);
        self::assertStringContainsString('Authorization: Bearer keeal_sk_test_fixture', $joined);
        self::assertStringContainsString('Idempotency-Key:', $joined);
    }

    public function test_create_session_sends_subscription_mode_body(): void
    {
        $fake = new FakeHttpTransport([
            ['status' => 200, 'body' => '{"id":"cs_sub","url":"https://pay.keeal.test/cs_sub"}'],
        ]);

        $this->fakeKeealCheckoutHttp($fake);

        $client = $this->app->make(KeealCheckout::class);

        $client->createSession([
            'mode' => 'subscription',
            'subscription_data' => [
                'price_id' => 'price_catalog_abc',
                'auto_charge_enabled' => true,
            ],
            'success_url' => 'https://shop.test/welcome',
            'cancel_url' => 'https://shop.test/pricing',
        ]);

        self::assertCount(1, $fake->requests);
        $body = json_decode($fake->requests[0]['body'] ?? '', true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('subscription', $body['mode']);
        self::assertSame('price_catalog_abc', $body['subscription_data']['price_id']);
        self::assertTrue($body['subscription_data']['auto_charge_enabled']);
    }

    public function test_throws_when_api_credentials_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not configured');

        $this->app['config']->set('keeal.api_key', '');
        $this->app['config']->set('keeal.base_url', 'https://api.keeal.test/api');

        $this->app->forgetInstance(KeealCheckout::class);
        $this->app->make(KeealCheckout::class);
    }
}
