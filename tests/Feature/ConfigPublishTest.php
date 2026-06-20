<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout\Tests\Feature;

use Keeal\LaravelCheckout\Tests\TestCase;

final class ConfigPublishTest extends TestCase
{
    public function test_config_is_registered(): void
    {
        $config = config('keeal');
        self::assertIsArray($config);
        self::assertArrayHasKey('api_key', $config);
        self::assertArrayHasKey('base_url', $config);
        self::assertArrayHasKey('webhook_secret', $config);
        self::assertArrayHasKey('mode', $config);
    }

    public function test_vendor_publish_tag_runs(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'keeal-config', '--force' => true])
            ->assertOk();

        $published = $this->app->configPath() . '/keeal.php';
        self::assertFileExists($published);
        self::assertStringContainsString('KEEAL_API_KEY', (string) file_get_contents($published));

        if (file_exists($published)) {
            @unlink($published);
        }
    }
}
