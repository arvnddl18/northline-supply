<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthController extends Controller
{
    /**
     * Provide a comprehensive system health check for Uptime Kuma and operations.
     * Evaluates Database PDO, Redis connection, and Queue backlog depth.
     */
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

        $queueStatus = 'ok';
        $queueError = null;
        $queueSize = null;
        // shortcut: Default backlog threshold 100, configurable via env for high-volume spikes
        $threshold = (int) env('QUEUE_BACKLOG_THRESHOLD', 100);

        try {
            $queueSize = Queue::size();
            if ($queueSize >= $threshold) {
                $queueStatus = 'warning';
            }
        } catch (Throwable $e) {
            $queueStatus = 'error';
            $queueError = $e->getMessage();
        }

        $isHealthy = $dbStatus === 'ok' && $redisStatus === 'ok' && $queueStatus !== 'error';

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
                'queue' => [
                    'connection' => config('queue.default'),
                    'status' => $queueStatus,
                    'size' => $queueSize,
                    'threshold' => $threshold,
                    'error' => $queueError,
                ],
            ],
        ], $isHealthy ? 200 : 503);
    }
}
