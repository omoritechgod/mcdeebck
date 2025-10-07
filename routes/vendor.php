<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Vendor\ServiceVendorController;
use App\Http\Controllers\Vendor\ServiceApartmentController;
use App\Http\Controllers\Vendor\RiderController;
use App\Http\Controllers\Vendor\MechanicController;
use App\Http\Controllers\Vendor\ProductVendorController;
use App\Http\Controllers\Vendor\FoodVendorController;
use App\Http\Controllers\Vendor\VendorFoodController;
use App\Http\Controllers\Vendor\RiderFoodController;



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

    // Food Vendor - Profile Setup
    Route::prefix('food')->group(function () {
        Route::post('/setup', [FoodVendorController::class, 'store']);
        Route::get('/profile', [FoodVendorController::class, 'profile']);
        Route::put('/profile', [FoodVendorController::class, 'update']);
        Route::patch('/toggle-open', [FoodVendorController::class, 'toggleOpen']);

        // Menu Management
        Route::post('/menu', [VendorFoodController::class, 'createMenu']);
        Route::get('/menu', [VendorFoodController::class, 'listMenu']);
        Route::put('/menu/{id}', [VendorFoodController::class, 'updateMenu']);
        Route::delete('/menu/{id}', [VendorFoodController::class, 'deleteMenu']);
        Route::patch('/menu/{id}/availability', [VendorFoodController::class, 'toggleAvailability']);

        // Order Management
        Route::get('/orders', [VendorFoodController::class, 'listOrders']);
        Route::get('/orders/{id}', [VendorFoodController::class, 'getOrder']);
        Route::patch('/orders/{id}/accept', [VendorFoodController::class, 'acceptOrder']);
        Route::patch('/orders/{id}/status', [VendorFoodController::class, 'updateOrderStatus']);
        Route::post('/orders/{id}/assign-rider', [VendorFoodController::class, 'assignRider']);

        // Available Riders
        Route::get('/available-riders', [VendorFoodController::class, 'availableRiders']);

        // Dashboard
        Route::get('/dashboard', [VendorFoodController::class, 'dashboard']);
    });

    // Rider - Food Delivery Operations
    Route::prefix('rider/food')->group(function () {
        Route::get('/orders/available', [RiderFoodController::class, 'availableOrders']);
        Route::get('/orders/assigned', [RiderFoodController::class, 'assignedOrders']);
        Route::get('/orders/{id}', [RiderFoodController::class, 'getOrder']);
        Route::post('/orders/{id}/accept', [RiderFoodController::class, 'acceptOrder']);
        Route::patch('/orders/{id}/pickup', [RiderFoodController::class, 'markPickedUp']);
        Route::patch('/orders/{id}/on-the-way', [RiderFoodController::class, 'markOnTheWay']);
        Route::patch('/orders/{id}/deliver', [RiderFoodController::class, 'markDelivered']);
        Route::patch('/availability', [RiderFoodController::class, 'toggleAvailability']);
        Route::post('/location', [RiderFoodController::class, 'updateLocation']);
        Route::get('/earnings', [RiderFoodController::class, 'earnings']);
        Route::get('/dashboard', [RiderFoodController::class, 'dashboard']);
    });
});
