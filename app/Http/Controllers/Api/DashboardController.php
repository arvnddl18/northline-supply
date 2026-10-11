<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Aggregated financial KPI metrics for merchant overview dashboard (S3-T1).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        // Total cash collected across all shop payments
        $totalCollected = (int) Payment::whereHas('order', fn($q) => $q->where('shop_id', $shop->id))
            ->where('status', Payment::STATUS_SUCCEEDED)
            ->sum('amount');

        // Total pending balance due on active orders (excluding cancelled, fully paid, or refunded)
        $pendingBalanceDue = (int) PaymentSchedule::whereHas('order', fn($q) => 
                $q->where('shop_id', $shop->id)->whereNotIn('payment_status', [
                    Order::STATUS_CANCELLED, 
                    Order::STATUS_PAID,
                    Order::STATUS_REFUNDED,
                ])
            )
            ->where('status', PaymentSchedule::STATUS_PENDING)
            ->sum('amount');

        // Upcoming scheduled charges
        $upcomingChargesQuery = PaymentSchedule::whereHas('order', fn($q) => 
                $q->where('shop_id', $shop->id)->where('payment_status', '!=', Order::STATUS_CANCELLED)
            )
            ->where('status', PaymentSchedule::STATUS_PENDING)
            ->where('due_at', '>=', now());

        $upcomingCount = (int) $upcomingChargesQuery->count();
        $upcomingTotal = (int) $upcomingChargesQuery->sum('amount');

        // Failed payments count & total
        $failedPaymentsQuery = Payment::whereHas('order', fn($q) => $q->where('shop_id', $shop->id))
            ->where('status', Payment::STATUS_FAILED);

        $failedCount = (int) $failedPaymentsQuery->count();
        $failedTotal = (int) $failedPaymentsQuery->sum('amount');

        return response()->json([
            'currency' => $shop->currency ?? 'AUD',
            'kpis' => [
                'total_collected_cents' => $totalCollected,
                'pending_balance_due_cents' => $pendingBalanceDue,
                'upcoming_scheduled_charges' => [
                    'count' => $upcomingCount,
                    'total_cents' => $upcomingTotal,
                ],
                'failed_payments' => [
                    'count' => $failedCount,
                    'total_cents' => $failedTotal,
                ],
            ],
        ]);
    }
}
