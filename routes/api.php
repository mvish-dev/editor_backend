<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware(['auth:sanctum', 'maintenance'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // User Settings
    Route::post('/user/profile', [\App\Http\Controllers\UserController::class, 'updateProfile']); // POST for FormData (file upload) usually easier than PUT
    Route::delete('/user/profile-picture', [\App\Http\Controllers\UserController::class, 'removeProfilePicture']);
    Route::put('/user/password', [\App\Http\Controllers\UserController::class, 'updatePassword']);
    Route::delete('/user', [\App\Http\Controllers\UserController::class, 'deleteAccount']);
});

Route::prefix('auth')->middleware('maintenance')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Password Reset
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-otp', [AuthController::class, 'verifyResetOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware(['auth:sanctum', 'maintenance'])->group(function () {
    // Designs
    Route::apiResource('designs', \App\Http\Controllers\DesignController::class);
    Route::post('/designs/generate', [\App\Http\Controllers\DesignController::class, 'generateImage']);

    // Tokens
    Route::get('/packages', [\App\Http\Controllers\TokenController::class, 'packages']);
    Route::get('/tokens/balance', [\App\Http\Controllers\TokenController::class, 'balance']);
    Route::post('/purchase', [\App\Http\Controllers\TokenController::class, 'purchase']);
    Route::get('/transactions', [\App\Http\Controllers\TokenController::class, 'history']);
    Route::get('/orders', [\App\Http\Controllers\TokenController::class, 'orders']);
    Route::get('/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'stats']);

    Route::get('/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'stats']);
    
    // Tickets
    Route::get('/tickets', [\App\Http\Controllers\TicketController::class, 'index']);
    Route::post('/tickets', [\App\Http\Controllers\TicketController::class, 'store']);

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\AdminController::class, 'overview']);
        
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users']);
        
        // Reports
        Route::get('/reports', [\App\Http\Controllers\AdminController::class, 'reports']);

        // Tickets
        Route::get('/tickets', [\App\Http\Controllers\AdminController::class, 'tickets']);
        Route::put('/tickets/{ticket}/status', [\App\Http\Controllers\AdminController::class, 'updateTicketStatus']);

        Route::post('/users', [\App\Http\Controllers\AdminController::class, 'storeUser']);
        Route::put('/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [\App\Http\Controllers\AdminController::class, 'destroyUser']);

        Route::post('/users/{user}/tokens', [\App\Http\Controllers\AdminController::class, 'adjustTokens']);
        Route::get('/templates/pending', [\App\Http\Controllers\AdminController::class, 'pendingTemplates']);
        Route::post('/templates/{design}/approve', [\App\Http\Controllers\AdminController::class, 'approveTemplate']);

        // Site Settings
        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index']);
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update']);

        // Categories
        Route::apiResource('categories', \App\Http\Controllers\CategoryController::class);
        // Templates
        Route::apiResource('templates', \App\Http\Controllers\TemplateController::class);
    });
});
