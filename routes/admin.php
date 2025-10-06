<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AuthController;

use App\Http\Controllers\Admin\AdminVendorController;

use App\Http\Controllers\Admin\AdminKYCVerificationController;

use App\Http\Controllers\Admin\AdminBookingController;

// Route::middleware(['auth:admin'])->group(function () {

// });
use App\Http\Controllers\Admin\AdminOrderController;

Route::middleware('auth:admin')->prefix('admin')->group(function () {
    Route::get('orders', [AdminOrderController::class, 'index']);
    Route::get('orders/{order}', [AdminOrderController::class, 'show']);
    Route::post('orders/{order}/dispute', [AdminOrderController::class, 'markDisputed']);
    Route::post('orders/{order}/refund', [AdminOrderController::class, 'refund']);
});




Route::middleware('auth:admin')->prefix('admin')->group(function () {
    Route::get('/kyc/verifications', [AdminKYCVerificationController::class, 'index']);
    Route::post('/kyc/verifications/{id}/approve', [AdminKYCVerificationController::class, 'approve']);
    Route::post('/kyc/verifications/{id}/reject', [AdminKYCVerificationController::class, 'reject']);

    Route::get('/apartment/bookings', [AdminBookingController::class, 'index']); // all bookings
    Route::get('/bookings/apartments', [AdminBookingController::class, 'apartmentBookings']); // apartment only
    Route::get('/bookings/{id}', [AdminBookingController::class, 'show']);
    Route::put('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);
});




Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/vendors', [AdminVendorController::class, 'index']);
});


Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'stats']);
});



Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


// Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
//     Route::get('/dashboard', [AdminDashboardController::class, 'stats']);
// });