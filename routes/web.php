<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Import the new controllers
use App\Http\Controllers\CityController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\BuildingTypeController;
use App\Http\Controllers\MapPlotController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ShopContractController;
use App\Http\Controllers\ShopRequestController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin grouped routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('cities', CityController::class);
        Route::resource('building-types', BuildingTypeController::class);
        Route::resource('maps', MapController::class);
        Route::resource('owners', OwnerController::class);
        Route::resource('map-plots', MapPlotController::class);
        Route::get('/map-plot-editor', function () { return Inertia::render('Admin/Maps/InteractiveMapPlotEditor'); })->name('map-plot-editor.index');
        Route::resource('shop-contracts', ShopContractController::class); // Added to admin group
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->only(['index', 'create', 'store']);
        Route::get('/maps/{map}/details', [MapController::class, 'getMapDetailsApi'])->name('maps.details.api'); // Added
        // Future admin routes can be added here
    });

    // User-accessible resource routes that require authentication but not admin prefix
    Route::resource('shop-requests', ShopRequestController::class);
    // Route::resource('shop-contracts', ShopContractController::class); // Commented out as it's now admin only

    // API-like routes for authenticated users (e.g., fetching data for Vue components)
    Route::get('/api/maps/{map}/active-contracts', [\App\Http\Controllers\ShopContractController::class, 'getActiveContractsForMap'])->name('api.maps.active-contracts');

    // User-facing map viewer page
    Route::get('/view-shops-map', function () { return \Inertia\Inertia::render('User/Maps/ViewMap'); })->name('user.view-shops-map');
});

// Other resource routes (public or different middleware)
// Route::resource('shop-contracts', ShopContractController::class); // Removed from here

require __DIR__.'/auth.php';
