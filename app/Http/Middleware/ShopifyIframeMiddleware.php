<?php

namespace App\Http\Middleware;

use App\Services\Shopify\ShopifyOAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopifyIframeMiddleware
{
    public function __construct(
        protected ShopifyOAuthService $oauthService
    ) {}

    /**
     * Set dynamic Content-Security-Policy frame-ancestors for Shopify embedded iframes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $shop = $request->query('shop');
        $validShop = $this->oauthService->isValidShopDomain($shop) ? $shop : null;

        $ancestors = 'https://admin.shopify.com';
        if ($validShop) {
            $ancestors .= " https://{$validShop}";
        }

        // Allow Shopify admin iframe embedding and remove clickjacking blocker for embedded app
        $response->headers->set('Content-Security-Policy', "frame-ancestors {$ancestors};");
        $response->headers->remove('X-Frame-Options');

        return $response;
    }
}
