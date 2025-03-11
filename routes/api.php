<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\WeatherController;

Log::info('API routes file is being loaded');

Route::get('/test', function () {
    Log::info('Test route is being accessed');
    return response()->json(['message' => 'API test route works!']);
});

Route::get('/ping', function () {
    Log::info('Ping route is being accessed');
    return response()->json(['message' => 'pong']);
});

Route::prefix('subscriptions')->group(function () {
    Route::post('/', [SubscriptionController::class, 'store']);
    Route::get('/', [SubscriptionController::class, 'index']);
    Route::get('/{id}', [SubscriptionController::class, 'show']);
    Route::put('/{id}', [SubscriptionController::class, 'update']);
    Route::delete('/{id}', [SubscriptionController::class, 'destroy']);
    Route::post('/unsubscribe/{id}', [SubscriptionController::class, 'unsubscribe']);
});

Route::prefix('weather')->group(function () {
    Route::get('/current', [WeatherController::class, 'current']);
    Route::get('/health', [WeatherController::class, 'health']);
}); 