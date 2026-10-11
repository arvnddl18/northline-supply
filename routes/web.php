<?php

use App\Http\Controllers\Admin\EmbeddedAdminController;
use App\Http\Controllers\Auth\ShopifyAuthController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Webhooks\ShopifyWebhookController;
use App\Http\Middleware\ShopifyIframeMiddleware;
use Illuminate\Support\Facades\Route;

// Embedded Admin Shell (S1-T3)
Route::get('/', [EmbeddedAdminController::class, 'index'])
    ->middleware(ShopifyIframeMiddleware::class)
    ->name('home');

Route::get('/health', HealthController::class);

// Shopify OAuth Installation Flow (S1-T2)
Route::get('/auth/install', [ShopifyAuthController::class, 'install'])->name('shopify.auth.install');
Route::get('/auth/callback', [ShopifyAuthController::class, 'callback'])->name('shopify.auth.callback');

// Shopify Webhooks
Route::post('/webhooks/shopify', [ShopifyWebhookController::class, 'handle'])->name('shopify.webhooks');
Route::post('/webhooks/app/uninstalled', [ShopifyWebhookController::class, 'handle'])->name('shopify.webhooks.uninstalled');

// Mandatory Shopify Privacy Webhooks (S3-T4, US-23)
Route::post('/webhooks/shopify/customers/data_request', [\App\Http\Controllers\Webhooks\ShopifyPrivacyWebhookController::class, 'customerDataRequest'])->name('shopify.webhooks.customer_data_request');
Route::post('/webhooks/shopify/customers/redact', [\App\Http\Controllers\Webhooks\ShopifyPrivacyWebhookController::class, 'customerRedact'])->name('shopify.webhooks.customer_redact');
Route::post('/webhooks/shopify/shop/redact', [\App\Http\Controllers\Webhooks\ShopifyPrivacyWebhookController::class, 'shopRedact'])->name('shopify.webhooks.shop_redact');

// Stripe Webhooks (S2-T2)
Route::post('/webhooks/stripe', [\App\Http\Controllers\Webhooks\StripeWebhookController::class, 'handle'])->name('stripe.webhooks');

// Balance Payment Link & Checkout (S2-T5) and Card Update (S4-T5)
Route::prefix('pay')->group(function () {
    Route::get('/balance/{schedule}', [\App\Http\Controllers\Customer\BalancePaymentController::class, 'show'])->name('pay.balance');
    Route::post('/balance/{schedule}/checkout', [\App\Http\Controllers\Customer\BalancePaymentController::class, 'checkout'])->name('pay.balance.checkout');
    Route::get('/card/update/{schedule}', [\App\Http\Controllers\Customer\CardUpdateController::class, 'show'])->name('pay.card.update');
    Route::post('/card/update/{schedule}', [\App\Http\Controllers\Customer\CardUpdateController::class, 'update'])->name('pay.card.update.post');
});

// Customer Self-Service Portal (S5-T3, US-19)
Route::post('/portal/request-link', [\App\Http\Controllers\Customer\CustomerPortalController::class, 'requestLink'])->name('customer.portal.request');
Route::get('/portal/{customer}', [\App\Http\Controllers\Customer\CustomerPortalController::class, 'show'])->name('customer.portal');

// Shopify App Proxy (S1-T7 & S1-T8)
Route::middleware([\App\Http\Middleware\VerifyShopifyAppProxy::class])->prefix('proxy')->group(function () {
    Route::get('/plan-info', [\App\Http\Controllers\Proxy\AppProxyController::class, 'planInfo'])->name('proxy.plan_info');
    Route::match(['get', 'post'], '/checkout', [\App\Http\Controllers\Proxy\AppProxyController::class, 'startCheckout'])->name('proxy.checkout');
});
