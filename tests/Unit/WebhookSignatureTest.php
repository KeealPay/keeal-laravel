<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Tests\Unit;

use Keeal\LaravelCheckout\Tests\TestCase;
use Keeal\LaravelCheckout\WebhookSignature;

final class WebhookSignatureTest extends TestCase
{
    public function test_verify_with_config_secret(): void
    {
        $raw = '{"type":"checkout.session.completed"}';
        $t = (string) time();
        $signed = $t . '.' . $raw;
        $secret = 'whsec_test_fixture_secret';
        $v1 = hash_hmac('sha256', $signed, $secret, false);

        self::assertTrue(WebhookSignature::verify($raw, 't=' . $t . ',v1=' . $v1));
    }

    public function test_verify_with_explicit_secret_overrides_config(): void
    {
        $this->app['config']->set('keeal.webhook_secret', 'wrong');

        $raw = '{"x":1}';
        $t = (string) time();
        $secret = 'whsec_explicit_only';
        $signed = $t . '.' . $raw;
        $v1 = hash_hmac('sha256', $signed, $secret, false);

        self::assertTrue(WebhookSignature::verify($raw, 't=' . $t . ',v1=' . $v1, $secret));
    }

    public function test_verify_fails_when_config_secret_empty(): void
    {
        $this->app['config']->set('keeal.webhook_secret', '');
        self::assertFalse(WebhookSignature::verify('{}', 't=1,v1=ab'));
    }
}
