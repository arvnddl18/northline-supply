<?php

/**
 * Sentry Configuration for Northwind Ceramics Payment App
 * Enforces strict PCI SAQ-A scope and data privacy sanitization.
 */

return [

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    // Capture release from git commit sha or env
    'release' => env('SENTRY_RELEASE'),

    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),

    'sample_rate' => (float) env('SENTRY_SAMPLE_RATE', 1.0),

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.1),

    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.0),

    // Strictly false to prevent leaking customer card or PII data
    'send_default_pii' => false,

    'controllers_base_namespace' => env('SENTRY_CONTROLLERS_BASE_NAMESPACE', 'App\\Http\\Controllers'),

    'ignore_exceptions' => [
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
    ],

    // Before send callback to scrub sensitive merchant keys, tokens, and payment data
    'before_send' => function (\Sentry\Event $event, ?\Sentry\EventHint $hint): ?\Sentry\Event {
        $request = $event->getRequest();
        if ($request && isset($request['headers'])) {
            $sensitiveHeaders = [
                'authorization',
                'x-shopify-hmac-sha256',
                'x-shopify-access-token',
                'stripe-signature',
            ];
            foreach ($sensitiveHeaders as $header) {
                if (isset($request['headers'][$header])) {
                    $request['headers'][$header] = '[REDACTED]';
                }
            }
            $event->setRequest($request);
        }

        // Scrub sensitive parameters in request data
        if ($request && isset($request['data']) && is_array($request['data'])) {
            $sensitiveKeys = [
                'password',
                'password_confirmation',
                'token',
                'access_token',
                'secret',
                'stripe_secret',
                'shopify_api_secret',
                'code',
                'card',
                'number',
                'cvv',
                'exp_month',
                'exp_year',
            ];
            array_walk_recursive($request['data'], function (&$value, $key) use ($sensitiveKeys) {
                if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                    $value = '[REDACTED]';
                }
            });
            $event->setRequest($request);
        }

        return $event;
    },

];
