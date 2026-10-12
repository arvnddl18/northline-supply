<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class Sprint3DemoSeeder extends Seeder
{
    /**
     * Seed Northwind Ceramics studio demo data across all payment lifecycle states (S3-T6).
     */
    public function run(): void
    {
        // 1. Ensure Northwind Ceramics shop exists
        $shop = Shop::firstOrCreate(
            ['shopify_domain' => 'northwind-ceramics.myshopify.com'],
            [
                'shopify_domain' => 'northwind-ceramics.myshopify.com',
                'access_token' => 'shpat_demo_northwind_access_token_melbourne',
                'installed_at' => now()->subDays(30),
                'currency' => 'AUD',
                'stripe_account_id' => 'acct_1NorthwindMelbourneDemo',
            ]
        );
        $stripeKey = env('STRIPE_KEY') ?: 'pk_test_northwind_demo';
        $stripeSecret = env('STRIPE_SECRET') ?: 'rk_test_northwind_demo';
        $shop->setStripeCredentials($stripeKey, $stripeSecret, 'acct_1NorthwindMelbourneDemo');

        // 2. Ensure Store Owner user exists
        $owner = User::firstOrCreate(
            ['email' => 'emma@northwindceramics.com'],
            [
                'name' => 'Emma Carter',
                'password' => bcrypt('demo_password_123'),
                'shop_id' => $shop->id,
                'email' => 'emma@northwindceramics.com',
                'role' => 'owner',
            ]
        );

        // 3. Ensure Standard 30% Deposit Plan exists
        $plan = Plan::firstOrCreate(
            ['shop_id' => $shop->id, 'name' => '30% Custom Ceramic Pre-order Deposit'],
            [
                'shop_id' => $shop->id,
                'name' => '30% Custom Ceramic Pre-order Deposit',
                'type' => 'deposit',
                'deposit_type' => 'percentage',
                'deposit_value' => 30,
                'active' => true,
            ]
        );

        // 4. Create realistic ceramic collector customers
        $customers = [
            'clara' => Customer::firstOrCreate(
                ['shop_id' => $shop->id, 'email' => 'clara.bow@melbournepottery.com'],
                [
                    'shop_id' => $shop->id,
                    'shopify_customer_id' => 'gid://shopify/Customer/1001',
                    'email' => 'clara.bow@melbournepottery.com',
                    'stripe_customer_id' => 'cus_demo_clara_1',
                    'default_payment_method' => 'pm_card_visa_demo_clara',
                ]
            ),
            'david' => Customer::firstOrCreate(
                ['shop_id' => $shop->id, 'email' => 'david.miller@sydneyceramics.com'],
                [
                    'shop_id' => $shop->id,
                    'shopify_customer_id' => 'gid://shopify/Customer/1002',
                    'email' => 'david.miller@sydneyceramics.com',
                    'stripe_customer_id' => 'cus_demo_david_2',
                    'default_payment_method' => 'pm_card_mastercard_demo_david',
                ]
            ),
            'sarah' => Customer::firstOrCreate(
                ['shop_id' => $shop->id, 'email' => 'sarah.jenkins@fitzroystudio.com.au'],
                [
                    'shop_id' => $shop->id,
                    'shopify_customer_id' => 'gid://shopify/Customer/1003',
                    'email' => 'sarah.jenkins@fitzroystudio.com.au',
                    'stripe_customer_id' => 'cus_demo_sarah_3',
                    'default_payment_method' => 'pm_card_visa_demo_sarah',
                ]
            ),
            'liam' => Customer::firstOrCreate(
                ['shop_id' => $shop->id, 'email' => 'liam.oconnor@brunswickclay.com'],
                [
                    'shop_id' => $shop->id,
                    'shopify_customer_id' => 'gid://shopify/Customer/1004',
                    'email' => 'liam.oconnor@brunswickclay.com',
                    'stripe_customer_id' => 'cus_demo_liam_4',
                    'default_payment_method' => 'pm_card_amex_demo_liam',
                ]
            ),
            'elena' => Customer::firstOrCreate(
                ['shop_id' => $shop->id, 'email' => 'elena.rostova@yarravalley.com.au'],
                [
                    'shop_id' => $shop->id,
                    'shopify_customer_id' => 'gid://shopify/Customer/1005',
                    'email' => 'elena.rostova@yarravalley.com.au',
                    'stripe_customer_id' => 'cus_demo_elena_5',
                    'default_payment_method' => 'pm_card_visa_demo_elena',
                ]
            ),
            'marcus' => Customer::firstOrCreate(
                ['shop_id' => $shop->id, 'email' => 'marcus.vance@carltonarts.com.au'],
                [
                    'shop_id' => $shop->id,
                    'shopify_customer_id' => 'gid://shopify/Customer/1006',
                    'email' => 'marcus.vance@carltonarts.com.au',
                    'stripe_customer_id' => 'cus_demo_marcus_6',
                ]
            ),
        ];

        // 5. Order 1: DEPOSIT PAID — BALANCE DUE (Stoneware Dinner Set, $320.00 AUD)
        $order1 = Order::updateOrCreate(
            ['shop_id' => $shop->id, 'shopify_order_id' => 'gid://shopify/Order/2001'],
            [
                'customer_id' => $customers['clara']->id,
                'plan_id' => $plan->id,
                'currency' => 'AUD',
                'total_amount' => 32000,
                'paid_amount' => 9600,
                'payment_status' => Order::STATUS_DEPOSIT_PAID,
                'created_at' => now()->subDays(5),
            ]
        );
        Payment::firstOrCreate(
            ['order_id' => $order1->id, 'stripe_payment_intent_id' => 'pi_demo_order1_deposit'],
            [
                'amount' => 9600,
                'status' => Payment::STATUS_SUCCEEDED,
            ]
        );
        PaymentSchedule::updateOrCreate(
            ['order_id' => $order1->id, 'sequence' => 1],
            [
                'amount' => 22400,
                'due_at' => now()->addDays(14),
                'status' => PaymentSchedule::STATUS_PENDING,
            ]
        );

        // 6. Order 2: FULLY PAID (Hand-carved Terracotta Vase, $180.00 AUD)
        $order2 = Order::updateOrCreate(
            ['shop_id' => $shop->id, 'shopify_order_id' => 'gid://shopify/Order/2002'],
            [
                'customer_id' => $customers['david']->id,
                'plan_id' => $plan->id,
                'currency' => 'AUD',
                'total_amount' => 18000,
                'paid_amount' => 18000,
                'payment_status' => Order::STATUS_PAID,
                'created_at' => now()->subDays(20),
            ]
        );
        Payment::firstOrCreate(
            ['order_id' => $order2->id, 'stripe_payment_intent_id' => 'pi_demo_order2_deposit'],
            [
                'amount' => 5400,
                'status' => Payment::STATUS_SUCCEEDED,
            ]
        );
        Payment::firstOrCreate(
            ['order_id' => $order2->id, 'stripe_payment_intent_id' => 'pi_demo_order2_balance'],
            [
                'amount' => 12600,
                'status' => Payment::STATUS_SUCCEEDED,
            ]
        );
        PaymentSchedule::updateOrCreate(
            ['order_id' => $order2->id, 'sequence' => 1],
            [
                'amount' => 12600,
                'due_at' => now()->subDays(2),
                'status' => PaymentSchedule::STATUS_SUCCEEDED,
            ]
        );

        // 7. Order 3: PARTIALLY REFUNDED (Fluted Ceramic Planter, $140.00 AUD)
        $order3 = Order::updateOrCreate(
            ['shop_id' => $shop->id, 'shopify_order_id' => 'gid://shopify/Order/2003'],
            [
                'customer_id' => $customers['sarah']->id,
                'plan_id' => $plan->id,
                'currency' => 'AUD',
                'total_amount' => 14000,
                'paid_amount' => 4200,
                'payment_status' => Order::STATUS_PARTIALLY_REFUNDED,
                'created_at' => now()->subDays(12),
            ]
        );
        $pay3 = Payment::firstOrCreate(
            ['order_id' => $order3->id, 'stripe_payment_intent_id' => 'pi_demo_order3_deposit'],
            [
                'amount' => 4200,
                'status' => Payment::STATUS_SUCCEEDED,
            ]
        );
        Refund::firstOrCreate(
            ['stripe_refund_id' => 're_demo_order3_partial'],
            [
                'payment_id' => $pay3->id,
                'shopify_refund_id' => 'gid://shopify/Refund/301',
                'amount' => 2100,
                'reason' => 'Minor exterior glaze fleck concession',
                'created_by' => $owner->id,
            ]
        );
        PaymentSchedule::updateOrCreate(
            ['order_id' => $order3->id, 'sequence' => 1],
            [
                'amount' => 9800,
                'due_at' => now()->addDays(7),
                'status' => PaymentSchedule::STATUS_PENDING,
            ]
        );

        // 8. Order 4: FULLY REFUNDED & SCHEDULE CANCELLED (Porcelain Tea Set, $250.00 AUD)
        $order4 = Order::updateOrCreate(
            ['shop_id' => $shop->id, 'shopify_order_id' => 'gid://shopify/Order/2004'],
            [
                'customer_id' => $customers['liam']->id,
                'plan_id' => $plan->id,
                'currency' => 'AUD',
                'total_amount' => 25000,
                'paid_amount' => 7500,
                'payment_status' => Order::STATUS_REFUNDED,
                'created_at' => now()->subDays(15),
            ]
        );
        $pay4 = Payment::firstOrCreate(
            ['order_id' => $order4->id, 'stripe_payment_intent_id' => 'pi_demo_order4_deposit'],
            [
                'amount' => 7500,
                'status' => Payment::STATUS_SUCCEEDED,
            ]
        );
        Refund::firstOrCreate(
            ['stripe_refund_id' => 're_demo_order4_full'],
            [
                'payment_id' => $pay4->id,
                'shopify_refund_id' => 'gid://shopify/Refund/302',
                'amount' => 7500,
                'reason' => 'Customer relocated interstate prior to throwing',
                'created_by' => $owner->id,
            ]
        );
        PaymentSchedule::updateOrCreate(
            ['order_id' => $order4->id, 'sequence' => 1],
            [
                'amount' => 17500,
                'due_at' => now()->addDays(5),
                'status' => PaymentSchedule::STATUS_CANCELLED,
            ]
        );

        // 9. Order 5: FAILED PAYMENT (Rustic Serving Platter, $120.00 AUD)
        $order5 = Order::updateOrCreate(
            ['shop_id' => $shop->id, 'shopify_order_id' => 'gid://shopify/Order/2005'],
            [
                'customer_id' => $customers['elena']->id,
                'plan_id' => $plan->id,
                'currency' => 'AUD',
                'total_amount' => 12000,
                'paid_amount' => 3600,
                'payment_status' => Order::STATUS_FAILED,
                'created_at' => now()->subDays(8),
            ]
        );
        Payment::firstOrCreate(
            ['order_id' => $order5->id, 'stripe_payment_intent_id' => 'pi_demo_order5_deposit'],
            [
                'amount' => 3600,
                'status' => Payment::STATUS_SUCCEEDED,
            ]
        );
        Payment::firstOrCreate(
            ['order_id' => $order5->id, 'stripe_payment_intent_id' => 'pi_demo_order5_balance_failed'],
            [
                'amount' => 8400,
                'status' => Payment::STATUS_FAILED,
            ]
        );
        PaymentSchedule::updateOrCreate(
            ['order_id' => $order5->id, 'sequence' => 1],
            [
                'amount' => 8400,
                'due_at' => now()->subDays(1),
                'status' => PaymentSchedule::STATUS_FAILED,
            ]
        );

        // 10. Order 6: PENDING DEPOSIT (Sculptural Ikebana Bowl, $210.00 AUD)
        Order::updateOrCreate(
            ['shop_id' => $shop->id, 'shopify_order_id' => 'gid://shopify/Order/2006'],
            [
                'customer_id' => $customers['marcus']->id,
                'plan_id' => $plan->id,
                'currency' => 'AUD',
                'total_amount' => 21000,
                'paid_amount' => 0,
                'payment_status' => Order::STATUS_PENDING_DEPOSIT,
                'created_at' => now()->subHours(3),
            ]
        );

        // 11. Populate rich Audit Trail
        AuditLog::firstOrCreate(
            ['shop_id' => $shop->id, 'action' => 'stripe.connected'],
            [
                'shop_id' => $shop->id,
                'action' => 'stripe.connected',
                'target_type' => Shop::class,
                'target_id' => (string) $shop->id,
                'meta' => ['stripe_account_id' => 'acct_1NorthwindMelbourneDemo'],
                'user_id' => $owner->id,
                'created_at' => now()->subDays(29),
            ]
        );
        AuditLog::firstOrCreate(
            ['shop_id' => $shop->id, 'action' => 'plan.created'],
            [
                'shop_id' => $shop->id,
                'action' => 'plan.created',
                'target_type' => Plan::class,
                'target_id' => (string) $plan->id,
                'meta' => ['name' => $plan->name, 'deposit_value' => 30],
                'user_id' => $owner->id,
                'created_at' => now()->subDays(25),
            ]
        );
        AuditLog::firstOrCreate(
            ['shop_id' => $shop->id, 'action' => 'refund.issued', 'target_id' => (string) $order3->id],
            [
                'shop_id' => $shop->id,
                'action' => 'refund.issued',
                'target_type' => Order::class,
                'target_id' => (string) $order3->id,
                'meta' => ['amount' => 2100, 'stripe_refund_id' => 're_demo_order3_partial'],
                'user_id' => $owner->id,
                'created_at' => now()->subDays(10),
            ]
        );
        AuditLog::firstOrCreate(
            ['shop_id' => $shop->id, 'action' => 'refund.issued', 'target_id' => (string) $order4->id],
            [
                'shop_id' => $shop->id,
                'action' => 'refund.issued',
                'target_type' => Order::class,
                'target_id' => (string) $order4->id,
                'meta' => ['amount' => 7500, 'stripe_refund_id' => 're_demo_order4_full'],
                'user_id' => $owner->id,
                'created_at' => now()->subDays(14),
            ]
        );

        // 12. Ceramic Club Subscription Plans & Demo Subscriptions (S5-T1..T3)
        $subPlanWheel = Plan::firstOrCreate(
            ['shop_id' => $shop->id, 'name' => 'Ceramic Club — Wheel Throwing Tier'],
            [
                'shop_id' => $shop->id,
                'name' => 'Ceramic Club — Wheel Throwing Tier',
                'type' => 'subscription',
                'deposit_type' => 'fixed',
                'deposit_value' => 6500, // $65.00 AUD/month
                'interval' => 'month',
                'interval_count' => 1,
                'active' => true,
            ]
        );

        $subPlanGlaze = Plan::firstOrCreate(
            ['shop_id' => $shop->id, 'name' => 'Ceramic Club — Glaze Master Tier'],
            [
                'shop_id' => $shop->id,
                'name' => 'Ceramic Club — Glaze Master Tier',
                'type' => 'subscription',
                'deposit_type' => 'fixed',
                'deposit_value' => 9500, // $95.00 AUD/month
                'interval' => 'month',
                'interval_count' => 1,
                'active' => true,
            ]
        );

        $subPlanGuild = Plan::firstOrCreate(
            ['shop_id' => $shop->id, 'name' => "Ceramic Club — Collector's Guild Tier"],
            [
                'shop_id' => $shop->id,
                'name' => "Ceramic Club — Collector's Guild Tier",
                'type' => 'subscription',
                'deposit_type' => 'fixed',
                'deposit_value' => 14500, // $145.00 AUD/month
                'interval' => 'month',
                'interval_count' => 1,
                'active' => true,
            ]
        );

        // Seed active subscriptions for Clara and Marcus, and paused for Elena
        Subscription::firstOrCreate(
            ['shop_id' => $shop->id, 'stripe_subscription_id' => 'sub_demo_clara_wheel'],
            [
                'shop_id' => $shop->id,
                'customer_id' => $customers['clara']->id,
                'plan_id' => $subPlanWheel->id,
                'stripe_subscription_id' => 'sub_demo_clara_wheel',
                'status' => Subscription::STATUS_ACTIVE,
                'next_renewal_at' => now()->addDays(18),
            ]
        );

        Subscription::firstOrCreate(
            ['shop_id' => $shop->id, 'stripe_subscription_id' => 'sub_demo_marcus_glaze'],
            [
                'shop_id' => $shop->id,
                'customer_id' => $customers['marcus']->id,
                'plan_id' => $subPlanGlaze->id,
                'stripe_subscription_id' => 'sub_demo_marcus_glaze',
                'status' => Subscription::STATUS_ACTIVE,
                'next_renewal_at' => now()->addDays(24),
            ]
        );

        Subscription::firstOrCreate(
            ['shop_id' => $shop->id, 'stripe_subscription_id' => 'sub_demo_elena_paused'],
            [
                'shop_id' => $shop->id,
                'customer_id' => $customers['elena']->id,
                'plan_id' => $subPlanWheel->id,
                'stripe_subscription_id' => 'sub_demo_elena_paused',
                'status' => Subscription::STATUS_PAUSED,
                'paused_until' => now()->addDays(45),
                'next_renewal_at' => now()->addDays(45),
            ]
        );
    }
}
