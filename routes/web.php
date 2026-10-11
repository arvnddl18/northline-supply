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
