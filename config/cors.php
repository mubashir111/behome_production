<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Production setup:
    |   FRONTEND_URL=https://yourdomain.com
    |   APP_URL=https://api.yourdomain.com   (or same domain if co-hosted)
    |
    | Webhook endpoints (/api/webhooks/*) receive POST requests from
    | Stripe/PayPal servers. They do NOT use CORS — browser clients never
    | call them. They are safe to exclude from CORS origins checks because
    | they rely on signature verification instead.
    */

    // Paths that should be handled by CORS middleware
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'storage/*',        // Images/files served cross-origin by the frontend
    ],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter([
        env('FRONTEND_URL', 'http://localhost:3000'),
        env('APP_URL',      'http://localhost:8000'),
    ]),

    // Allow pattern-based origins for subdomain setups (e.g. *.yourdomain.com)
    // Add entries like '#^https://.*\.yourdomain\.com$#' when needed
    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Accept',
        'Authorization',
        'X-Requested-With',
        'x-api-key',
    ],

    'exposed_headers' => [],

    // Cache preflight for 2 hours (production-friendly)
    'max_age' => 7200,

    // Required for Sanctum cookie-based auth and credentialed requests
    'supports_credentials' => true,

];
