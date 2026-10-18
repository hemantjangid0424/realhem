<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name', 'RealHem API'),
        'status' => 'operational',
        'api_version' => '1.0',
        'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
    ]);
});
