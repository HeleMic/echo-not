<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\ApplicationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // ----------------------------------------------------
    // APPLICATIONS ROUTES
    // ----------------------------------------------------
    Route::apiResource('applications', ApplicationController::class);

    // ----------------------------------------------------
    // APPLICATION'S API KEYS ROUTES
    // ----------------------------------------------------
    Route::apiResource('api-keys', ApiKeyController::class);
    Route::post('/api-keys/{id}/revoke', [ApiKeyController::class, 'revoke'])->name('api-keys.revoke');
});