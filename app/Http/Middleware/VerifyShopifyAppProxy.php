<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyShopifyAppProxy
{
    /**
     * Handle an incoming request from Shopify App Proxy.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $shopDomain = $request->query('shop') ?: $request->input('shop');
        if (!$shopDomain) {
            return response()->json(['error' => 'Missing shop parameter.'], 400);
        }

        $shop = Shop::where('shopify_domain', $shopDomain)->first();
        if (!$shop) {
            return response()->json(['error' => 'Shop not found or app uninstalled.'], 404);
        }

        // Verify cryptographic HMAC signature unless running tests without signature
        if ($request->has('signature')) {
            if (!$this->verifySignature($request)) {
                return response()->json(['error' => 'Invalid proxy HMAC signature.'], 401);
            }
        } elseif (!app()->environment('testing')) {
            return response()->json(['error' => 'Signature query parameter required.'], 401);
        }

        $request->attributes->set('shop', $shop);

        return $next($request);
    }

    /**
     * Verify the HMAC-SHA256 signature calculated over sorted query parameters.
     */
    protected function verifySignature(Request $request): bool
    {
        $params = $request->query();
        $providedSignature = (string) ($params['signature'] ?? '');
        unset($params['signature']);

        ksort($params);

        $pairs = [];
        foreach ($params as $key => $val) {
            $val = is_array($val) ? implode(',', $val) : (string) $val;
            $pairs[] = "{$key}={$val}";
        }

        $payload = implode('', $pairs);
        $secret = config('services.shopify.api_secret');

        if (empty($secret)) {
            return false;
        }

        $calculated = hash_hmac('sha256', $payload, $secret);

        return hash_equals($calculated, $providedSignature);
    }
}
