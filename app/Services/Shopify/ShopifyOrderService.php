<?php

namespace App\Services\Shopify;

use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ShopifyOrderService
{
    /**
     * Create a pre-order in Shopify with financial_status: partially_paid.
     * shortcut: Uses Shopify Admin REST Orders API for MVP simplicity; upgrade to GraphQL staged draft orders in Sprint 3.
     *
     * @param Shop $shop
     * @param array{email: string, variant_id: string|int, quantity: int, total_cents: int, deposit_cents: int, currency: string, title?: string} $data
     * @return string|null Shopify Order GID or numeric ID string
     */
    public function createDepositOrder(Shop $shop, array $data): ?string
    {
        $accessToken = $shop->access_token;
        if (empty($accessToken)) {
            Log::warning("Cannot create Shopify order: Shop {$shop->shopify_domain} has no access token.");
            return null;
        }

        $currency = strtoupper($data['currency'] ?? 'AUD');
        $totalDecimal = number_format($data['total_cents'] / 100, 2, '.', '');
        $depositDecimal = number_format($data['deposit_cents'] / 100, 2, '.', '');
        $variantId = str_replace('gid://shopify/ProductVariant/', '', (string) $data['variant_id']);

        $payload = [
            'order' => [
                'email' => $data['email'],
                'financial_status' => 'partially_paid',
                'currency' => $currency,
                'tags' => 'deposit-paid, balance-due',
                'note' => "Pre-order deposit of {$currency} {$depositDecimal} captured via Stripe. Balance pending completion.",
                'line_items' => [
                    [
                        'variant_id' => is_numeric($variantId) ? (int) $variantId : null,
                        'title' => $data['title'] ?? 'Pre-order Handmade Ceramics',
                        'quantity' => (int) ($data['quantity'] ?? 1),
                        'price' => $totalDecimal,
                    ],
                ],
                'transactions' => [
                    [
                        'kind' => 'capture',
                        'status' => 'success',
                        'amount' => $depositDecimal,
                        'gateway' => 'Northwind Deposit App',
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post("https://{$shop->shopify_domain}/admin/api/2024-01/orders.json", $payload);

            if ($response->successful()) {
                $orderId = (string) ($response->json('order.id') ?? '');
                $gid = "gid://shopify/Order/{$orderId}";
                Log::info("Created Shopify deposit order {$gid} for shop {$shop->shopify_domain}");
                return $gid;
            }

            Log::error("Failed to create Shopify deposit order: " . $response->body(), [
                'status' => $response->status(),
                'shop' => $shop->shopify_domain,
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error("Exception creating Shopify deposit order: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Synchronize tags and metafields to Shopify Order record (S2-T4).
     */
    public function syncPaymentStatus(Order $order, ?string $targetStatus = null): bool
    {
        $shop = $order->shop;
        if (!$shop || empty($shop->access_token) || empty($order->shopify_order_id)) {
            return false;
        }

        $numericId = str_replace('gid://shopify/Order/', '', (string) $order->shopify_order_id);
        if (!is_numeric($numericId)) {
            return false;
        }

        $status = $targetStatus ?: $order->payment_status;

        $tags = match ($status) {
            Order::STATUS_PAID => 'paid, balance-paid, deposit-paid',
            Order::STATUS_BALANCE_DUE => 'deposit-paid, balance-due',
            Order::STATUS_FAILED => 'deposit-paid, balance-failed',
            default => 'deposit-paid, balance-due',
        };

        $schedule = $order->paymentSchedules()->where('status', '!=', 'cancelled')->first();
        $balanceLink = null;
        if ($schedule && $schedule->status !== 'succeeded') {
            $balanceLink = URL::signedRoute('pay.balance', ['schedule' => $schedule->id], now()->addDays(30));
        }

        $metafields = [
            [
                'namespace' => 'northwind_deposit',
                'key' => 'status',
                'value' => $order->payment_status,
                'type' => 'single_line_text_field',
            ],
            [
                'namespace' => 'northwind_deposit',
                'key' => 'deposit_cents',
                'value' => (string) $order->paid_amount,
                'type' => 'number_integer',
            ],
            [
                'namespace' => 'northwind_deposit',
                'key' => 'balance_cents',
                'value' => (string) $order->remainingBalance(),
                'type' => 'number_integer',
            ],
        ];

        if ($schedule && $schedule->due_at) {
            $metafields[] = [
                'namespace' => 'northwind_deposit',
                'key' => 'due_at',
                'value' => $schedule->due_at->toIso8601String(),
                'type' => 'date_time',
            ];
        }

        if ($balanceLink) {
            $metafields[] = [
                'namespace' => 'northwind_deposit',
                'key' => 'balance_link',
                'value' => $balanceLink,
                'type' => 'url',
            ];
        }

        $updatePayload = [
            'order' => [
                'id' => (int) $numericId,
                'tags' => $tags,
                'metafields' => $metafields,
            ],
        ];

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->access_token,
                'Content-Type' => 'application/json',
            ])->timeout(10)->put("https://{$shop->shopify_domain}/admin/api/2024-01/orders/{$numericId}.json", $updatePayload);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Failed to sync payment status to Shopify order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Append a specific tag to a Shopify order (e.g. installment-failed, dispute-opened).
     */
    public function addOrderTag(Order $order, string $tag): bool
    {
        $shop = $order->shop;
        if (!$shop || empty($shop->access_token) || empty($order->shopify_order_id)) {
            return false;
        }

        $numericId = str_replace('gid://shopify/Order/', '', (string) $order->shopify_order_id);
        if (!is_numeric($numericId)) {
            return false;
        }

        try {
            $existing = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->access_token,
            ])->timeout(10)->get("https://{$shop->shopify_domain}/admin/api/2024-01/orders/{$numericId}.json");

            $currentTags = (string) ($existing->json('order.tags') ?? '');
            $newTags = trim($currentTags . ', ' . $tag, ', ');

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->access_token,
                'Content-Type' => 'application/json',
            ])->timeout(10)->put("https://{$shop->shopify_domain}/admin/api/2024-01/orders/{$numericId}.json", [
                'order' => [
                    'id' => (int) $numericId,
                    'tags' => $newTags,
                ],
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Failed to add tag to Shopify order: " . $e->getMessage());
            return false;
        }
    }
}
