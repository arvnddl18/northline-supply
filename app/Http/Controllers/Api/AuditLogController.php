<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * List audit logs for the authenticated shop (US-24, S3-T5).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $query = $shop->auditLogs()->with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        $logs = $query->paginate(25);

        return response()->json($logs);
    }
}
