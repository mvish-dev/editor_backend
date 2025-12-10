<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Designs
    Route::apiResource('designs', \App\Http\Controllers\DesignController::class);
    Route::post('/designs/generate', [\App\Http\Controllers\DesignController::class, 'generateImage']);

    // Tokens
    Route::get('/packages', [\App\Http\Controllers\TokenController::class, 'packages']);
    Route::post('/purchase', [\App\Http\Controllers\TokenController::class, 'purchase']);
    Route::get('/transactions', [\App\Http\Controllers\TokenController::class, 'history']);

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users']);
        Route::post('/users/{user}/tokens', [\App\Http\Controllers\AdminController::class, 'adjustTokens']);
        Route::get('/templates/pending', [\App\Http\Controllers\AdminController::class, 'pendingTemplates']);
        Route::post('/templates/{design}/approve', [\App\Http\Controllers\AdminController::class, 'approveTemplate']);
    });
});
