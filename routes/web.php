<?php

use App\Http\Controllers\Auth\ShopifyAuthController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Webhooks\ShopifyWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => config('app.name'),
        'status' => 'online',
        'version' => '1.0.0',
        'client' => 'Northwind Ceramics',
    ]);
});

Route::get('/health', HealthController::class);

// Shopify OAuth Installation Flow (S1-T2)
Route::get('/auth/install', [ShopifyAuthController::class, 'install'])->name('shopify.auth.install');
Route::get('/auth/callback', [ShopifyAuthController::class, 'callback'])->name('shopify.auth.callback');

// Shopify Webhooks
Route::post('/webhooks/shopify', [ShopifyWebhookController::class, 'handle'])->name('shopify.webhooks');
Route::post('/webhooks/app/uninstalled', [ShopifyWebhookController::class, 'handle'])->name('shopify.webhooks.uninstalled');
