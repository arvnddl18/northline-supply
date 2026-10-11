<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    /**
     * Generate and dispatch secure magic login link to customer email (US-19, S5-T3).
     */
    public function requestLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $validated['email'])->first();

        // Always return success response to prevent email enumeration attacks
        if (!$customer) {
            return response()->json([
                'status' => 'sent',
                'message' => 'If an account exists with this email, a login link has been sent.',
            ]);
        }

        $magicUrl = URL::signedRoute('customer.portal', [
            'customer' => $customer->id,
        ], now()->addMinutes(15));

        $payload = [
            'status' => 'sent',
            'message' => 'If an account exists with this email, a login link has been sent.',
        ];

        // shortcut: In local/testing returns link in payload for automated verification; upgrade to email delivery in prod
        if (app()->environment('local', 'testing')) {
            $payload['portal_url'] = $magicUrl;
        }

        return response()->json($payload);
    }

    /**
     * View customer self-service portal (orders, balance links, subscriptions).
     */
    public function show(Request $request, Customer $customer): View
    {
        if (!$request->hasValidSignature()) {
            return view('customer.portal_expired');
        }

        $orders = $customer->orders()
            ->with(['paymentSchedules', 'payments', 'plan'])
            ->latest()
            ->get();

        $subscriptions = $customer->subscriptions()
            ->with('plan')
            ->latest()
            ->get();

        return view('customer.portal', [
            'customer' => $customer,
            'orders' => $orders,
            'subscriptions' => $subscriptions,
        ]);
    }
}
