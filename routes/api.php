<?php

use App\Http\Controllers\Api\AdminOverviewController;
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
});
