<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestCorrelationIdMiddleware
{
    /**
     * Handle an incoming request and assign a shared correlation ID across logs and headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID') ?: (string) Str::uuid();

        // Share correlation ID into Laravel 11 contextual logging
        Log::shareContext([
            'request_id' => $requestId,
        ]);

        if (app()->bound('sentry')) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($requestId): void {
                $scope->setTag('request_id', $requestId);
            });
        }

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
