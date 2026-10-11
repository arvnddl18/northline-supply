<?php

use App\Http\Controllers\HealthController;
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
