<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use App\Services\Shopify\ShopifyOAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyShopifySessionToken
{
    public function __construct(
        protected ShopifyOAuthService $oauthService
    ) {}

    /**
     * Handle an incoming request authenticated with a Shopify App Bridge session token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization') ?: $request->header('X-Shopify-Session-Token');
        if (!$authHeader) {
            return response()->json([
                'error' => 'Missing Shopify session token in request header.',
            ], 401);
        }

        $token = str_starts_with($authHeader, 'Bearer ')
            ? substr($authHeader, 7)
            : $authHeader;

        $payload = $this->oauthService->verifySessionToken($token);
        if (!$payload || empty($payload['shop_domain'])) {
            return response()->json([
                'error' => 'Invalid or expired Shopify session token.',
            ], 401);
        }

        $shop = Shop::where('shopify_domain', $payload['shop_domain'])
            ->whereNull('uninstalled_at')
            ->first();

        if (!$shop) {
            return response()->json([
                'error' => 'Shop not found or application uninstalled.',
            ], 403);
        }

        // Bind authenticated shop and token payload to the request
        $request->attributes->set('shop', $shop);
        $request->attributes->set('shopify_session', $payload);

        $user = $shop->users()->first();
        if ($user) {
            $request->attributes->set('user', $user);
            $request->attributes->set('user_id', $user->id);
        }

        return $next($request);
    }
}
