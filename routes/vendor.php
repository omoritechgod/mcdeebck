<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Vendor\ServiceVendorController;
use App\Http\Controllers\Vendor\ServiceApartmentController;
use App\Http\Controllers\Vendor\RiderController;
use App\Http\Controllers\Vendor\MechanicController;
use App\Http\Controllers\Vendor\ProductVendorController;
use App\Http\Controllers\Vendor\FoodVendorController;



Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/register', [VendorController::class, 'register']);
    // Route::get('/live', [VendorController::class, 'isLive']);

    // Service Vendor
    Route::post('/service/setup', [ServiceVendorController::class, 'store']);
    Route::get('/service/profile', [ServiceVendorController::class, 'profile']);

    


    // Service Apartment
    Route::post('/apartment/setup', [ServiceApartmentController::class, 'setup']);
    Route::get('/apartment/profile', [ServiceApartmentController::class, 'profile']);

    // Rider
    Route::post('/rider/setup', [RiderController::class, 'completeRegistration']);
    Route::get('/rider/profile', [RiderController::class, 'profile']);

    // Mechanic
    Route::post('/mechanic/setup', [MechanicController::class, 'register']);
    Route::get('/mechanic/profile', [MechanicController::class, 'show']);

    // Product Vendor
    Route::post('/product/setup', [ProductVendorController::class, 'store']);

    // Food Vendor
    Route::prefix('food')->group(function () {
        Route::post('/setup', [FoodVendorController::class, 'store']);
        Route::get('/profile', [FoodVendorController::class, 'profile']);
    });
});
