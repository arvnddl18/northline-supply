<?php

use App\Http\Controllers\Api\AdminOverviewController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\StripeConnectionController;
use App\Http\Middleware\VerifyShopifySessionToken;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::middleware([VerifyShopifySessionToken::class])->group(function () {
    Route::get('/overview', [AdminOverviewController::class, 'show'])->name('api.admin.overview');

    // Stripe Connection API (Owner only per US-15)
    Route::get('/stripe/status', [StripeConnectionController::class, 'status'])->name('api.stripe.status');
    Route::middleware([\App\Http\Middleware\EnsureOwnerRole::class])->group(function () {
        Route::post('/stripe/connect', [StripeConnectionController::class, 'connect'])->name('api.stripe.connect');
        Route::post('/stripe/disconnect', [StripeConnectionController::class, 'disconnect'])->name('api.stripe.disconnect');
    });

    // Plans API
    Route::get('/plans', [PlanController::class, 'index'])->name('api.plans.index');
    Route::post('/plans', [PlanController::class, 'store'])->name('api.plans.store');
    Route::post('/plans/preview', [PlanController::class, 'preview'])->name('api.plans.preview');
    Route::get('/plans/{id}', [PlanController::class, 'show'])->name('api.plans.show');
    Route::put('/plans/{id}', [PlanController::class, 'update'])->name('api.plans.update');
    Route::delete('/plans/{id}', [PlanController::class, 'destroy'])->name('api.plans.destroy');

    // Dashboard Financial KPIs API (S3-T1, US-07)
    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'index'])->name('api.dashboard');

    // Orders & Payment Status API (US-05, US-06)
    Route::get('/orders', [\App\Http\Controllers\Api\OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/orders/{id}', [\App\Http\Controllers\Api\OrderController::class, 'show'])->name('api.orders.show');
    Route::post('/orders/{id}/payment-link', [\App\Http\Controllers\Api\OrderController::class, 'paymentLink'])->name('api.orders.payment_link');
    Route::post('/orders/{id}/refund', [\App\Http\Controllers\Api\OrderController::class, 'refund'])
        ->middleware(\App\Http\Middleware\EnsureOwnerRole::class)
        ->name('api.orders.refund');

    // Ceramic Club Subscriptions API (US-12, US-21, S5-T1, S5-T2)
    Route::get('/subscriptions', [\App\Http\Controllers\Api\SubscriptionController::class, 'index'])->name('api.subscriptions.index');
    Route::get('/subscriptions/{id}', [\App\Http\Controllers\Api\SubscriptionController::class, 'show'])->name('api.subscriptions.show');
    Route::post('/subscriptions/{id}/pause', [\App\Http\Controllers\Api\SubscriptionController::class, 'pause'])->name('api.subscriptions.pause');
    Route::post('/subscriptions/{id}/resume', [\App\Http\Controllers\Api\SubscriptionController::class, 'resume'])->name('api.subscriptions.resume');
    Route::post('/subscriptions/{id}/skip', [\App\Http\Controllers\Api\SubscriptionController::class, 'skip'])->name('api.subscriptions.skip');
    Route::post('/subscriptions/{id}/cancel', [\App\Http\Controllers\Api\SubscriptionController::class, 'cancel'])->name('api.subscriptions.cancel');

    // CSV Transaction Export API (US-14, S5-T5)
    Route::get('/transactions/export', [\App\Http\Controllers\Api\TransactionExportController::class, 'export'])->name('api.transactions.export');

    // Email Templates Customization API (US-09, S2-T7)
    Route::get('/email-templates', [\App\Http\Controllers\Api\EmailTemplateController::class, 'index'])->name('api.email_templates.index');
    Route::put('/email-templates/{key}', [\App\Http\Controllers\Api\EmailTemplateController::class, 'update'])->name('api.email_templates.update');
    Route::post('/email-templates/{key}/preview', [\App\Http\Controllers\Api\EmailTemplateController::class, 'preview'])->name('api.email_templates.preview');
    Route::post('/email-templates/{key}/test-send', [\App\Http\Controllers\Api\EmailTemplateController::class, 'testSend'])->name('api.email_templates.test_send');

    // Audit Log API (S3-T5, US-24)
    Route::get('/audit-logs', [\App\Http\Controllers\Api\AuditLogController::class, 'index'])->name('api.audit_logs.index');
});
