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
        // Future admin routes can be added here
    });
});

// Other resource routes (not part of this admin group for now)
Route::resource('map-plots', MapPlotController::class);
Route::resource('shop-contracts', ShopContractController::class);
Route::resource('shop-requests', ShopRequestController::class);

require __DIR__.'/auth.php';
