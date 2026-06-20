<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Tests\Feature;

use Keeal\Checkout\KeealCheckoutPublic;
use Keeal\LaravelCheckout\Facades\Keeal;
use Keeal\LaravelCheckout\Facades\KeealPublic;
use Keeal\LaravelCheckout\Tests\Fakes\FakeHttpTransport;
use Keeal\LaravelCheckout\Tests\TestCase;

final class FacadeTest extends TestCase
{
    public function test_keeal_facade_delegates_to_client(): void
    {
        $fake = new FakeHttpTransport([
            ['status' => 200, 'body' => '{"id":"cs_facade","url":"https://pay.test/x"}'],
        ]);
        $this->fakeKeealCheckoutHttp($fake);

        $result = Keeal::createSession([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => 'Item'],
                        'unit_amount' => 500,
                    ],
                    'quantity' => 2,
                ],
            ],
        ]);

        self::assertSame('cs_facade', $result['id']);
    }

    public function test_keeal_public_facade_works(): void
    {
        $body = json_encode([
            'id' => 'int',
            'sessionId' => 'cs_1',
            'contractorId' => 'c',
            'lineItems' => [],
            'amountCents' => 100,
            'currency' => 'USD',
            'status' => 'open',
            'successUrl' => null,
            'cancelUrl' => null,
            'customerEmail' => null,
        ], JSON_THROW_ON_ERROR);

        $this->app->instance(KeealCheckoutPublic::class, new KeealCheckoutPublic([
            'baseUrl' => 'https://api.keeal.test/api',
            'http' => new FakeHttpTransport([
                ['status' => 200, 'body' => $body],
            ]),
        ]));

        $session = KeealPublic::retrieveSession('cs_1');
        self::assertSame('cs_1', $session['sessionId']);
    }
}
