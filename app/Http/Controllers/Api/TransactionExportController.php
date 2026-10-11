<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionExportController extends Controller
{
    /**
     * Export shop financial transactions to CSV (US-14, S5-T5).
     */
    public function export(Request $request): StreamedResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $query = Payment::whereHas('order', fn($q) => $q->where('shop_id', $shop->id))
            ->with(['order.customer'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->query('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->query('to_date'));
        }

        $filename = "transactions_{$shop->shopify_domain}_" . now()->format('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // CSV Header Row
            fwrite($handle, "Payment ID,Date (UTC),Order ID,Shopify Order ID,Customer Email,Amount (Decimal),Currency,Status,Stripe Payment Intent,Failure Code\n");

            $query->chunk(100, function ($payments) use ($handle) {
                foreach ($payments as $payment) {
                    $order = $payment->order;
                    $decimalAmount = number_format($payment->amount / 100, 2, '.', '');

                    fputcsv($handle, [
                        $payment->id,
                        $payment->created_at->toIso8601String(),
                        $order?->id ?? '',
                        $order?->shopify_order_id ?? '',
                        $order?->customer?->email ?? '',
                        $decimalAmount,
                        $order?->currency ?? 'AUD',
                        $payment->status,
                        $payment->stripe_payment_intent_id,
                        $payment->failure_code ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
