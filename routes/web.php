<?php

use App\Http\Controllers\EmailPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name', 'RealHem API'),
        'status' => 'operational',
        'api_version' => '1.0',
        'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
    ]);
});

if (app()->environment('local', 'testing') || config('app.debug')) {
    Route::prefix('dev/mail-preview')->group(function () {
        Route::get('/', [EmailPreviewController::class, 'index'])->name('mail.preview.index');
        Route::get('/{template}', [EmailPreviewController::class, 'show'])->name('mail.preview.show');
    });
}

if (app()->environment('local', 'testing') || config('app.debug')) {
    Route::prefix('dev/mail-preview')->group(function () {
        Route::get('/', [EmailPreviewController::class, 'index'])->name('mail.preview.index');
        Route::get('/{template}', [EmailPreviewController::class, 'show'])->name('mail.preview.show');
    });
}
