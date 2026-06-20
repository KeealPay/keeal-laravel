<?php

declare(strict_types=1);

namespace Keeal\LaravelCheckout;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Keeal\Checkout\KeealCheckout;
use Keeal\Checkout\KeealCheckoutPublic;

final class KeealCheckoutServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/keeal.php' => config_path('keeal.php'),
            ], 'keeal-config');
        }

        $router->aliasMiddleware('keeal.webhook', Http\Middleware\VerifyKeealWebhookSignature::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/keeal.php', 'keeal');

        $this->app->singleton(KeealCheckout::class, function (Application $app): KeealCheckout {
            $apiKey = (string) $app->make('config')->get('keeal.api_key');
            $baseUrl = (string) $app->make('config')->get('keeal.base_url');

            if ($apiKey === '' || $baseUrl === '') {
                throw new \InvalidArgumentException(
                    'Keeal is not configured: set KEEAL_API_KEY and KEEAL_BASE_URL (or keeal.api_key / keeal.base_url).'
                );
            }

            /** @var array<string, string> $headers */
            $headers = (array) $app->make('config')->get('keeal.default_headers', []);

            return new KeealCheckout([
                'apiKey' => $apiKey,
                'baseUrl' => $baseUrl,
                'defaultHeaders' => $headers,
            ]);
        });

        $this->app->singleton(KeealCheckoutPublic::class, function (Application $app): KeealCheckoutPublic {
            $baseUrl = (string) $app->make('config')->get('keeal.base_url');
            if ($baseUrl === '') {
                throw new \InvalidArgumentException(
                    'Keeal public client requires KEEAL_BASE_URL (or keeal.base_url).'
                );
            }

            /** @var array<string, string> $headers */
            $headers = (array) $app->make('config')->get('keeal.default_headers', []);

            return new KeealCheckoutPublic([
                'baseUrl' => $baseUrl,
                'defaultHeaders' => $headers,
            ]);
        });
    }
}
