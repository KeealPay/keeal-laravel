<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Mode (test vs live)
    |--------------------------------------------------------------------------
    |
    | Similar to Stripe's testmode flag: use "test" for staging, sandboxes, and
    | local development; use "live" for production. This value does not change
    | how the HTTP client talks to Keeal — you still point base_url at the right
    | host and use the API key issued for that environment. Use mode in your
    | app to branch behavior (e.g. skip fulfillment emails in test).
    |
    | Allowed: test, live
    |
    */

    'mode' => env('KEEAL_MODE', 'live'),

    /*
    |--------------------------------------------------------------------------
    | Secret API key
    |--------------------------------------------------------------------------
    |
    | Your Keeal secret key (keeal_sk_…). Used only for server-side calls.
    | Never expose this value to the browser or mobile clients.
    |
    */

    'api_key' => env('KEEAL_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | API base URL
    |--------------------------------------------------------------------------
    |
    | Must include the /api path segment, e.g. https://api.example.com/api
    |
    */

    'base_url' => env('KEEAL_BASE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Webhook signing secret
    |--------------------------------------------------------------------------
    |
    | From Keeal dashboard (whsec_…). Used by VerifyKeealWebhookSignature middleware.
    |
    */

    'webhook_secret' => env('KEEAL_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Default HTTP headers
    |--------------------------------------------------------------------------
    |
    | Optional extra headers sent on every API request (e.g. correlation ids).
    |
    */

    'default_headers' => [],

];
