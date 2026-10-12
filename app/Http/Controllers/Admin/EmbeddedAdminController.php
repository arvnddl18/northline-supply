<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\Shopify\ShopifyOAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmbeddedAdminController extends Controller
{
    public function __construct(
        protected ShopifyOAuthService $oauthService
    ) {}

    /**
     * Handle the root embedded admin app entry point.
     */
    public function index(Request $request): JsonResponse|RedirectResponse|View
    {
        $shopDomain = $request->query('shop');

        // If JSON format is requested, return API online status
        if ($request->wantsJson()) {
            return response()->json([
                'app' => config('app.name'),
                'status' => 'online',
                'version' => '1.0.0',
                'client' => 'Northwind Ceramics',
            ]);
        }

        // If no shop query is provided, redirect to the interactive demo / testing landing page
        if (!$shopDomain) {
            return redirect()->route('demo.hub');
        }

        // Validate domain format
        if (!$this->oauthService->isValidShopDomain($shopDomain)) {
            return response()->json([
                'error' => 'Invalid shop domain parameter.',
            ], 400);
        }

        // Check if shop has completed installation and has active access token
        $shop = Shop::where('shopify_domain', $shopDomain)
            ->whereNull('uninstalled_at')
            ->first();

        if (!$shop || empty($shop->access_token)) {
            $host = $request->query('host');
            $installUrl = route('shopify.auth.install', [
                'shop' => $shopDomain,
                'host' => $host,
            ]);

            return redirect()->away($installUrl);
        }

        $apiKey = config('services.shopify.api_key') ?: env('SHOPIFY_API_KEY');
        $host = $request->query('host', '');

        $devSessionToken = null;
        if (app()->environment('local', 'testing')) {
            $devSessionToken = $this->oauthService->generateSessionToken($shopDomain);
        }

        return view('app', [
            'apiKey' => $apiKey,
            'shop' => $shopDomain,
            'host' => $host,
            'currency' => $shop->currency ?? 'AUD',
            'devSessionToken' => $devSessionToken,
        ]);
    }
}
