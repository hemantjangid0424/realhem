<?php

use Illuminate\Support\Facades\Route;

// Admin SPA (matches /admin and /admin/*)
Route::get('/admin/{any?}', function () {
    return view('admin-spa');
})->where('any', '.*')->name('admin.spa');

// Client SPA (matches / and any route that is not admin, api, etc.)
Route::get('/{any?}', function () {
    return view('client-spa');
})->where('any', '^(?!api|admin|storage|up|_boost).*$')->name('client.spa');
