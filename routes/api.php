<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UserAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Client User Mobile OTP Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/send-otp', [UserAuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [UserAuthController::class, 'verifyOtp']);
    Route::post('/complete-profile', [UserAuthController::class, 'completeProfile']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', [UserAuthController::class, 'me']);
        Route::put('/profile', [UserAuthController::class, 'updateProfile']);
        Route::post('/logout', [UserAuthController::class, 'logout']);
    });
});

// Location & Real Estate Geographic Discovery APIs
Route::prefix('locations')->group(function () {
    Route::get('/cities', [LocationController::class, 'cities']);
    Route::get('/localities', [LocationController::class, 'localities']);
    Route::get('/search', [LocationController::class, 'search']);
    Route::get('/search-cities', [LocationController::class, 'searchCities']);
    Route::get('/search-projects', [LocationController::class, 'searchProjects']);
    Route::match(['get', 'post'], '/detect', [LocationController::class, 'detect']);
    Route::get('/nearby', [LocationController::class, 'nearby']);
    Route::get('/resolve', [LocationController::class, 'resolve']);
});

// Property Marketplace APIs
Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/search/suggestions', [PropertyController::class, 'suggestions']);
Route::get('/properties/{property}', [PropertyController::class, 'show']);
// Slug-based single property lookup (preferred, SEO-friendly)
Route::get('/property/{property:slug}', [PropertyController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('/user/profile', [UserAuthController::class, 'updateProfile']);
    Route::post('/properties', [PropertyController::class, 'store']);
    Route::post('/properties/upload-photos', [PropertyController::class, 'uploadPhotos']);
    Route::get('/user/properties', [PropertyController::class, 'myProperties']);
    Route::put('/properties/{property}', [PropertyController::class, 'update']);
    Route::patch('/properties/{property}/status', [PropertyController::class, 'updateStatus']);
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);
});

// Public Branding & Customization Settings API
Route::get('/settings', [SettingController::class, 'publicIndex']);

// Admin SPA API Routes
Route::prefix('admin')->group(function () {
    Route::post('/auth/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/auth/me', [AdminAuthController::class, 'me']);
        Route::post('/auth/logout', [AdminAuthController::class, 'logout']);
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/users', [AdminDashboardController::class, 'users']);
        Route::get('/settings', [SettingController::class, 'adminIndex']);
        Route::post('/settings', [SettingController::class, 'update']);
    });
});
