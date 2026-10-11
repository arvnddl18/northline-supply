<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\Shopify\ShopifyOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShopifyAuthController extends Controller
{
    public function __construct(
        protected ShopifyOAuthService $oauthService
    ) {}

    /**
     * Initiate Shopify OAuth installation flow.
     */
    public function install(Request $request): RedirectResponse|Response
    {
        $shop = $request->query('shop');

        if (!$this->oauthService->isValidShopDomain($shop)) {
            return response('Invalid shop domain format. Must be an active .myshopify.com domain.', 400);
        }

        // If HMAC is provided on the installation entrypoint, verify it
        if ($request->has('hmac') && !$this->oauthService->verifyQueryHmac($request->query())) {
            return response('Invalid HMAC signature on install request.', 401);
        }

        $state = Str::random(32);
        Cache::put("shopify_oauth_state_{$state}", $shop, now()->addMinutes(15));

        $authUrl = $this->oauthService->buildAuthorizationUrl($shop, $state);

        return redirect()->away($authUrl);
    }

    /**
     * Handle Shopify OAuth callback, verify HMAC, and store encrypted token.
     */
    public function callback(Request $request): RedirectResponse|Response
    {
        $shop = $request->query('shop');
        $code = $request->query('code');
        $state = $request->query('state');

        if (!$shop || !$code || !$state) {
            return response('Missing required OAuth parameters.', 400);
        }

        if (!$this->oauthService->isValidShopDomain($shop)) {
            return response('Invalid shop domain format.', 400);
        }

        // 1. Verify state nonce from cache
        $cachedShop = Cache::pull("shopify_oauth_state_{$state}");
        if (!$cachedShop || $cachedShop !== $shop) {
            return response('Invalid or expired OAuth state nonce.', 403);
        }

        // 2. Cryptographically verify Shopify HMAC signature
        if (!$this->oauthService->verifyQueryHmac($request->query())) {
            return response('HMAC signature verification failed.', 401);
        }

        // 3. Exchange temporary auth code for permanent offline access token
        try {
            $tokenData = $this->oauthService->exchangeCodeForAccessToken($shop, $code);
        } catch (\Throwable $e) {
            return response('Failed to exchange authorization code with Shopify: ' . $e->getMessage(), 500);
        }

        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            return response('No access token returned by Shopify.', 500);
        }

        // 4. Upsert Shop record (access_token automatically encrypted at rest via Eloquent encrypted cast)
        Shop::updateOrCreate(
            ['shopify_domain' => $shop],
            [
                'access_token' => $accessToken,
                'installed_at' => now(),
                'uninstalled_at' => null,
                'currency' => 'AUD',
            ]
        );

        $host = $request->query('host');
        $redirectUrl = $host
            ? "/?shop={$shop}&host={$host}"
            : "/?shop={$shop}";

        return redirect()->away($redirectUrl);
    }
}
