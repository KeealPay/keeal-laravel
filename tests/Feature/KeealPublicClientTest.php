<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Tests\Feature;

use Keeal\Checkout\HttpTransportInterface;
use Keeal\Checkout\KeealCheckoutPublic;
use Keeal\LaravelCheckout\Tests\TestCase;

final class KeealPublicClientTest extends TestCase
{
    public function test_public_client_resolves_without_secret_key(): void
    {
        $this->app->instance(KeealCheckoutPublic::class, new KeealCheckoutPublic([
            'baseUrl' => 'https://api.keeal.test/api',
            'http' => new class implements HttpTransportInterface
            {
                public function send(string $method, string $url, ?string $body, array $headerLines): array
                {
                    return [
                        'status' => 200,
                        'body' => '{"id":"int","sessionId":"cs_public","contractorId":"c","lineItems":[],"amountCents":100,"currency":"USD","status":"open","successUrl":null,"cancelUrl":null,"customerEmail":null}',
                    ];
                }
            },
        ]));

        $public = $this->app->make(KeealCheckoutPublic::class);
        $session = $public->retrieveSession('cs_public');

        self::assertSame('cs_public', $session['sessionId']);
    }

    public function test_public_client_requires_base_url(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->app['config']->set('keeal.base_url', '');
        $this->app->forgetInstance(KeealCheckoutPublic::class);
        $this->app->make(KeealCheckoutPublic::class);
    }
}
