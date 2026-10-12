<?php

namespace Tests\Feature;

use App\Jobs\ProcessStripeWebhookJob;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SubscriptionsAndPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;
    protected User $owner;
    protected User $staff;
    protected Customer $customer;
    protected Plan $subPlan;
    protected Subscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.shopify.api_key' => 'test_key',
            'services.shopify.api_secret' => 'test_secret',
        ]);

        $this->shop = Shop::create([
            'shopify_domain' => 'portal.myshopify.com',
            'access_token' => 'shpat_sub_portal_token',
            'installed_at' => now(),
            'currency' => 'AUD',
        ]);
        $this->shop->setStripeCredentials('pk_test_sample', 'rk_test_sample', 'acct_sub');

        $this->owner = User::create([
            'shop_id' => $this->shop->id,
            'email' => 'emma@northwindceramics.com',
            'role' => 'owner',
        ]);

        $this->staff = User::create([
            'shop_id' => $this->shop->id,
            'email' => 'assistant@northwindceramics.com',
            'role' => 'staff',
        ]);

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'member@ceramicclub.com',
            'stripe_customer_id' => 'cus_sub_member_1',
            'default_payment_method' => 'pm_sub_card_1',
        ]);

        $this->subPlan = Plan::create([
            'shop_id' => $this->shop->id,
            'name' => 'Monthly Clay Kit Club',
            'type' => 'subscription',
            'interval' => 'month',
            'interval_count' => 1,
            'deposit_value' => 6500, // $65.00
            'active' => true,
        ]);

        $this->subscription = Subscription::create([
            'shop_id' => $this->shop->id,
            'customer_id' => $this->customer->id,
            'plan_id' => $this->subPlan->id,
            'stripe_subscription_id' => 'sub_stripe_123',
            'status' => Subscription::STATUS_ACTIVE,
            'next_renewal_at' => now()->addMonth(),
        ]);
    }

    protected function token(): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => "https://{$this->shop->shopify_domain}/admin",
            'dest' => "https://{$this->shop->shopify_domain}",
            'aud' => config('services.shopify.api_key'),
            'sub' => '123',
            'exp' => time() + 3600,
            'nbf' => time() - 60,
        ];

        $h = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $p = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $secret = config('services.shopify.api_secret');
        $s = rtrim(strtr(base64_encode(hash_hmac('sha256', "{$h}.{$p}", $secret, true)), '+/', '-_'), '=');

        return "{$h}.{$p}.{$s}";
    }

    public function test_subscriptions_lifecycle_pause_resume_skip_cancel(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/subscriptions/*' => Http::response(['status' => 'paused'], 200),
        ]);

        $token = $this->token();

        // 1. Pause
        $pauseRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/subscriptions/{$this->subscription->id}/pause");
        $pauseRes->assertStatus(200);
        $this->subscription->refresh();
        $this->assertTrue($this->subscription->isPaused());

        // 2. Resume
        $resumeRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/subscriptions/{$this->subscription->id}/resume");
        $resumeRes->assertStatus(200);
        $this->subscription->refresh();
        $this->assertTrue($this->subscription->isActive());

        // 3. Skip next cycle
        $prevRenewal = $this->subscription->next_renewal_at->copy();
        $skipRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/subscriptions/{$this->subscription->id}/skip");
        $skipRes->assertStatus(200);
        $this->subscription->refresh();
        $this->assertTrue($this->subscription->next_renewal_at->isAfter($prevRenewal));

        // 4. Cancel
        $cancelRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/subscriptions/{$this->subscription->id}/cancel");
        $cancelRes->assertStatus(200);
        $this->subscription->refresh();
        $this->assertTrue($this->subscription->isCancelled());
    }

    public function test_invoice_payment_succeeded_provisions_shopify_order(): void
    {
        Http::fake([
            'https://portal.myshopify.com/admin/api/*' => Http::response([
                'order' => ['id' => 9901, 'tags' => 'deposit-paid, balance-due'],
            ], 200),
        ]);

        $event = WebhookEvent::create([
            'source' => 'stripe',
            'external_id' => 'evt_invoice_test_1',
            'topic' => 'invoice.payment_succeeded',
            'payload' => [
                'type' => 'invoice.payment_succeeded',
                'data' => [
                    'object' => [
                        'subscription' => $this->subscription->stripe_subscription_id,
                        'amount_paid' => 6500,
                        'currency' => 'aud',
                        'period_end' => time() + (30 * 86400),
                    ],
                ],
            ],
            'status' => WebhookEvent::STATUS_PENDING,
            'received_at' => now(),
        ]);

        $job = new ProcessStripeWebhookJob($event->id);
        $job->handle(app(\App\Services\Shopify\ShopifyOrderService::class), app(\App\Services\Email\EmailService::class));

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'subscription.renewed',
            'target_id' => (string) $this->subscription->id,
        ]);
    }

    public function test_customer_portal_magic_link_flow(): void
    {
        // 1. Request magic link
        $reqRes = $this->postJson('/portal/request-link', [
            'email' => $this->customer->email,
        ]);
        $reqRes->assertStatus(200)
            ->assertJson(['status' => 'sent']);

        $magicUrl = $reqRes->json('portal_url');
        $this->assertNotNull($magicUrl);

        // 2. Access portal via signed link
        $portalRes = $this->get($magicUrl);
        $portalRes->assertStatus(200)
            ->assertSee($this->customer->email)
            ->assertSee('Ceramic Club Subscriptions');
    }

    public function test_staff_role_permissions_restriction(): void
    {
        $token = $this->token();

        // When user is staff, refunds and stripe connect are forbidden (403)
        $this->owner->update(['role' => 'staff']);

        $refundRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/orders/999/refund', [
                'amount' => 1000,
            ]);
        $refundRes->assertStatus(403)
            ->assertJsonStructure(['error']);

        $connectRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/stripe/connect', [
                'stripe_publishable_key' => 'pk_test_sample',
                'stripe_secret_key' => 'rk_test_sample',
            ]);
        $connectRes->assertStatus(403)
            ->assertJsonStructure(['error']);

        // When restored to owner, check moves past role guard
        $this->owner->update(['role' => 'owner']);
        $passRefund = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/orders/999/refund', [
                'amount' => 1000,
            ]);
        // Returns 404 because order 999 does not exist, proving role guard was passed!
        $passRefund->assertStatus(404);
    }

    public function test_csv_transaction_export_streams_headers_and_data(): void
    {
        $order = Order::create([
            'shop_id' => $this->shop->id,
            'shopify_order_id' => 'gid://shopify/Order/8888',
            'customer_id' => $this->customer->id,
            'currency' => 'AUD',
            'total_amount' => 20000,
            'paid_amount' => 20000,
            'payment_status' => Order::STATUS_PAID,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'stripe_payment_intent_id' => 'pi_export_test_1',
            'amount' => 20000,
            'status' => Payment::STATUS_SUCCEEDED,
        ]);

        $token = $this->token();
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->get('/api/transactions/export');

        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        $content = $response->streamedContent();

        $this->assertStringContainsString('Payment ID,Date (UTC),Order ID,Shopify Order ID', $content);
        $this->assertStringContainsString('pi_export_test_1', $content);
        $this->assertStringContainsString('member@ceramicclub.com', $content);
    }

    public function test_subscription_tier_change_with_stripe_prorations(): void
    {
        $newPlan = Plan::create([
            'shop_id' => $this->shop->id,
            'name' => 'Deluxe Glaze Kit Tier',
            'type' => 'subscription',
            'interval' => 'month',
            'interval_count' => 1,
            'deposit_value' => 9500, // $95.00
            'active' => true,
        ]);

        Http::fake([
            'https://api.stripe.com/v1/subscriptions/sub_stripe_123' => Http::response([
                'id' => 'sub_stripe_123',
                'items' => [
                    'data' => [
                        [
                            'id' => 'si_stripe_item_123',
                            'price' => ['product' => 'prod_ceramic_club'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $token = $this->token();
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/subscriptions/{$this->subscription->id}/change-tier", [
                'plan_id' => $newPlan->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('subscription.plan_id', $newPlan->id)
            ->assertJsonPath('delta_cents', 3000); // 9500 - 6500 = +3000 cents

        $this->assertEquals($newPlan->id, $this->subscription->fresh()->plan_id);

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'subscription.tier_changed',
        ]);
    }

    public function test_customer_portal_interactive_actions_via_signed_routes(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/subscriptions/*' => Http::response(['id' => 'sub_stripe_123'], 200),
        ]);

        // 1. Pause subscription via signed portal route
        $pauseUrl = URL::signedRoute('customer.portal.subscription.pause', [
            'customer' => $this->customer->id,
            'subscription' => $this->subscription->id,
        ], now()->addMinutes(15));

        $resPause = $this->post($pauseUrl, ['months' => 2]);
        $resPause->assertRedirect();
        $this->subscription->refresh();
        $this->assertEquals(Subscription::STATUS_PAUSED, $this->subscription->status);
        $this->assertNotNull($this->subscription->paused_until);

        // 2. Resume subscription via signed route
        $resumeUrl = URL::signedRoute('customer.portal.subscription.resume', [
            'customer' => $this->customer->id,
            'subscription' => $this->subscription->id,
        ], now()->addMinutes(15));

        $resResume = $this->post($resumeUrl);
        $resResume->assertRedirect();
        $this->subscription->refresh();
        $this->assertEquals(Subscription::STATUS_ACTIVE, $this->subscription->status);

        // 3. Skip next renewal cycle
        $oldRenewal = $this->subscription->next_renewal_at;
        $skipUrl = URL::signedRoute('customer.portal.subscription.skip', [
            'customer' => $this->customer->id,
            'subscription' => $this->subscription->id,
        ], now()->addMinutes(15));

        $resSkip = $this->post($skipUrl);
        $resSkip->assertRedirect();
        $this->subscription->refresh();
        $this->assertTrue($this->subscription->next_renewal_at->gt($oldRenewal));

        // 4. Cancel subscription via portal
        $cancelUrl = URL::signedRoute('customer.portal.subscription.cancel', [
            'customer' => $this->customer->id,
            'subscription' => $this->subscription->id,
        ], now()->addMinutes(15));

        $resCancel = $this->post($cancelUrl);
        $resCancel->assertRedirect();
        $this->subscription->refresh();
        $this->assertEquals(Subscription::STATUS_CANCELLED, $this->subscription->status);

        // 5. Tampered or unsigned access is rejected (403)
        $tamperedUrl = $cancelUrl . '&tampered=1';
        $resTampered = $this->post($tamperedUrl);
        $resTampered->assertStatus(403);
    }

    public function test_staff_role_cannot_cancel_subscription_but_can_pause(): void
    {
        $token = $this->token();

        Http::fake([
            'https://api.stripe.com/v1/subscriptions/*' => Http::response(['id' => 'sub_stripe_123'], 200),
        ]);

        // When staff tries to cancel: HTTP 403
        $this->owner->update(['role' => 'staff']);
        $resCancelStaff = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/subscriptions/{$this->subscription->id}/cancel");

        $resCancelStaff->assertStatus(403)
            ->assertJsonStructure(['error']);

        // When staff pauses: HTTP 200 permitted
        $resPauseStaff = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/subscriptions/{$this->subscription->id}/pause");

        $resPauseStaff->assertStatus(200);

        // When restored to owner, cancel is permitted
        $this->owner->update(['role' => 'owner']);
        $resCancelOwner = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/subscriptions/{$this->subscription->id}/cancel");

        $resCancelOwner->assertStatus(200);
        $this->assertEquals(Subscription::STATUS_CANCELLED, $this->subscription->fresh()->status);
    }
}
