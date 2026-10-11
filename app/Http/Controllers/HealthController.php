<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $dbStatus = 'ok';
        $dbError = null;
        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $dbStatus = 'error';
            $dbError = $e->getMessage();
        }

        $redisStatus = 'ok';
        $redisError = null;
        try {
            Redis::ping();
        } catch (Throwable $e) {
            $redisStatus = 'error';
            $redisError = $e->getMessage();
        }

        $isHealthy = $dbStatus === 'ok' && $redisStatus === 'ok';

        return response()->json([
            'status' => $isHealthy ? 'ok' : 'degraded',
            'app' => config('app.name'),
            'environment' => config('app.env'),
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => [
                    'connection' => config('database.default'),
                    'status' => $dbStatus,
                    'error' => $dbError,
                ],
                'redis' => [
                    'status' => $redisStatus,
                    'error' => $redisError,
                ],
            ],
        ], $isHealthy ? 200 : 503);
    }
}
