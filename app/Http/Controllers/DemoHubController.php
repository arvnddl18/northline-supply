<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class DemoHubController extends Controller
{
    /**
     * Display the Northwind Ceramics interactive local testing hub.
     */
    public function index(Request $request): View
    {
        $shop = Shop::where('shopify_domain', 'northwind-ceramics.myshopify.com')->first();
        $customer = Customer::with('orders.paymentSchedules')->first();
        $schedule = PaymentSchedule::with('order')->where('status', PaymentSchedule::STATUS_PENDING)->first()
            ?? PaymentSchedule::with('order')->first();

        $stats = [
            'orders' => Order::count(),
            'plans' => Plan::count(),
            'customers' => Customer::count(),
            'schedules' => PaymentSchedule::count(),
            'subscriptions' => Subscription::count(),
        ];

        $signedPortalUrl = $customer
            ? URL::signedRoute('customer.portal', ['customer' => $customer->id], now()->addHours(24))
            : null;

        $signedBalanceUrl = $schedule
            ? URL::signedRoute('pay.balance', ['schedule' => $schedule->id], now()->addHours(24))
            : null;

        $signedCardUrl = $schedule
            ? URL::signedRoute('pay.card.update', ['schedule' => $schedule->id], now()->addHours(24))
            : null;

        $adminUrl = url('/?shop=northwind-ceramics.myshopify.com');

        return view('demo', [
            'shop' => $shop,
            'customer' => $customer,
            'schedule' => $schedule,
            'stats' => $stats,
            'adminUrl' => $adminUrl,
            'signedPortalUrl' => $signedPortalUrl,
            'signedBalanceUrl' => $signedBalanceUrl,
            'signedCardUrl' => $signedCardUrl,
        ]);
    }

    /**
     * Display the simulated Northwind Ceramics storefront PDP.
     */
    public function storefront(Request $request): View
    {
        $shop = Shop::where('shopify_domain', 'northwind-ceramics.myshopify.com')->first();
        $plan = Plan::active()->first() ?? Plan::first();

        return view('storefront', [
            'shop' => $shop,
            'plan' => $plan,
        ]);
    }
}
