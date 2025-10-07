<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ApartmentController;
// use App\Http\Controllers\ApartmentBookingController;

use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\FoodMenuController;
use App\Http\Controllers\FoodOrderController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\GpsLogController;
use App\Http\Controllers\RideSettingController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\SendOtpController;
use App\Http\Controllers\Auth\VerifyOtpController;
use App\Http\Controllers\Api\ErrorLogController;
use App\Http\Controllers\Vendor\ComplianceController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\LiveVendorController;
use App\Http\Controllers\API\ProfilePictureController;

use App\Http\Controllers\ListingController;

use App\Http\Controllers\PublicListingController;

use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Webhook\FlutterwaveWebhookController;
use App\Http\Controllers\Vendor\ServiceVendorController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\Vendor\ServicePricingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Marketplace\ProductController as MarketplaceProductController;
use App\Http\Controllers\VendorOrderController;



Route::middleware('auth:sanctum')->prefix('vendor')->group(function () {
    Route::get('orders', [VendorOrderController::class, 'index']);
    Route::get('orders/{order}', [VendorOrderController::class, 'show']);
    Route::post('orders/{order}/accept', [VendorOrderController::class, 'accept']);
    Route::post('orders/{order}/reject', [VendorOrderController::class, 'reject']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::delete('/cart/{cart}', [CartController::class, 'destroy']);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders/checkout', [OrderController::class, 'checkoutFromCart']);
    Route::post('orders/{order}/complete', [OrderController::class, 'markCompleted']);
});




Route::get('marketplace/products', [MarketplaceProductController::class, 'index']);

Route::middleware('auth:sanctum')->prefix('vendor')->group(function () {
    Route::apiResource('products', ProductController::class);
});




// Public: vendor pricing list to display on vendor profile
Route::get('/service-vendors/{id}/pricings', [ServiceVendorController::class, 'pricings']);

// Vendor-side pricing CRUD
Route::middleware('auth:sanctum')->prefix('vendor')->group(function () {
    Route::get('/service-pricings',  [ServicePricingController::class, 'index']);
    Route::post('/service-pricings', [ServicePricingController::class, 'store']);
    Route::put('/service-pricings/{id}', [ServicePricingController::class, 'update']);
    Route::delete('/service-pricings/{id}', [ServicePricingController::class, 'destroy']);
});



// Public service vendors
Route::get('/service-vendors', [ServiceVendorController::class, 'index']);
Route::get('/service-vendors/{id}', [ServiceVendorController::class, 'show']);


// ================== Service Orders ==================
Route::middleware('auth:sanctum')->group(function () {
    // Step 1: User creates a service order
    Route::post('/service-orders', [ServiceOrderController::class, 'store']);

    // Step 1b: User views own orders
    Route::get('/service-orders/my', [ServiceOrderController::class, 'myOrders']);

    // Step 2: Vendor views incoming requests
    Route::get('/vendor/service-orders', [ServiceOrderController::class, 'vendorRequests']);

    // Step 3: Vendor responds (accept / decline / busy)
    Route::post('/service-orders/{id}/respond', [ServiceOrderController::class, 'vendorRespond']);

    // Step 4: User initiates payment after vendor accepts
    Route::post('/service-orders/{id}/pay', [ServiceOrderController::class, 'initiatePayment']);

    // Step 6: User marks service as completed (release vendor payment)
    Route::post('/service-orders/{id}/completed', [ServiceOrderController::class, 'markCompleted']);
});


// Step 5: Webhook from Flutterwave (public route, signature verified inside controller)
// Route::post('/flutterwave/webhook', [ServiceOrderController::class, 'flutterwaveWebhook']);


// Payment initiation
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookings/{id}/pay', [PaymentController::class, 'payForBooking']);
        // E-commerce order payments
    Route::post('/orders/{id}/pay', [PaymentController::class, 'payForOrder']);
    Route::post('/orders/{order}/release-escrow', [PaymentController::class, 'releaseEscrow']);
    Route::post('/orders/{order}/refund', [PaymentController::class, 'refundOrder']);
});

Route::post('/flutterwave/webhook', [FlutterwaveWebhookController::class, 'handle']);

// Manual trigger (frontend can call after redirect)
Route::middleware('auth:sanctum')->post('/flutterwave/manual-trigger', [\App\Http\Controllers\Webhook\FlutterwaveWebhookController::class, 'manualTrigger']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/apartment/bookings', [BookingController::class, 'store']);
    Route::get('/apartment/bookings/my', [BookingController::class, 'myBookings']);
});


Route::get('/apartments', [PublicListingController::class, 'index']);
Route::get('/apartments/{id}', [PublicListingController::class, 'show']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/listings', [ListingController::class, 'index']);
    Route::post('/listings', [ListingController::class, 'store']);
});





Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile-picture/upload', [ProfilePictureController::class, 'upload']);
    Route::get('/profile-picture', [ProfilePictureController::class, 'get']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/vendors/live', [LiveVendorController::class, 'index']);
    Route::get('/vendors/live/{id}', [LiveVendorController::class, 'show']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/otp/send-phone', [OtpController::class, 'sendPhoneOtp']);
    Route::post('/otp/verify-phone', [OtpController::class, 'verifyPhoneOtp']);
});

Route::post('/log-error', function (Request $request) {
    \Log::error('Frontend Error', [
        'context' => $request->input('context'),
        'message' => $request->input('message'),
        'stack' => $request->input('stack'),
        'url' => $request->input('url'),
        'extra' => $request->input('extra'),
    ]);
    return response()->json(['message' => 'Error logged']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('vendor/compliance')->group(function () {
        Route::get('/status', [\App\Http\Controllers\Vendor\ComplianceController::class, 'status']);
        Route::post('/upload-document', [\App\Http\Controllers\Vendor\ComplianceController::class, 'uploadDocument']);
        Route::post('/submit-review', [\App\Http\Controllers\Vendor\ComplianceController::class, 'submitReview']);
    });
});


    
Route::middleware('auth:sanctum')->group(function () {

    // Route::post('/verify/send-otp', [SendOtpController::class, 'sendEmailOtp']);
    // Route::post('/verify/confirm-otp', [VerifyOtpController::class, 'confirmOtp']);
});


Route::post('/log-error', [ErrorLogController::class, 'store']);


Route::get('/test-api', fn () => 'API working');




Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'update']);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wallet', [WalletController::class, 'wallet']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('/wallet/transfer', [WalletController::class, 'transfer']);
    Route::post('/wallet/fund', [WalletController::class, 'fundWallet']);
});

Route::post('/wallet/webhook', [WalletController::class, 'handleWebhook']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/verify/initiate', [VerificationController::class, 'initiate']);
    Route::post('/verify/submit', [VerificationController::class, 'submit']);
    Route::get('/verify/status', [VerificationController::class, 'status']);
});

// Food - Public endpoints
Route::get('/food/vendors', [FoodMenuController::class, 'listVendors']);
Route::get('/food/vendors/{id}', [FoodMenuController::class, 'getVendor']);
Route::get('/food/menus', [FoodMenuController::class, 'index']);
Route::get('/food/menus/{id}', [FoodMenuController::class, 'show']);

// Food - Customer endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/food/orders', [FoodOrderController::class, 'store']);
    Route::get('/food/orders', [FoodOrderController::class, 'index']);
    Route::get('/food/orders/{id}', [FoodOrderController::class, 'show']);
    Route::post('/food/orders/{id}/complete', [FoodOrderController::class, 'complete']);
    Route::post('/food/orders/{id}/cancel', [FoodOrderController::class, 'cancel']);
    Route::post('/food/orders/{id}/pay', [PaymentController::class, 'payForFoodOrder']);
});

// Ride
Route::post('/ride/request', [RideController::class, 'requestRide']);
Route::put('/ride/update-status', [RideController::class, 'updateStatus']);
Route::get('/ride/history', [RideController::class, 'history']);
Route::post('/ride/online', [RideController::class, 'goOnline']);
Route::post('/ride/offline', [RideController::class, 'goOffline']);
Route::post('/ride/rate', [RideController::class, 'rate']);

// Ping
Route::get('/ping', fn () => response()->json(['message' => 'pong']));


Route::middleware('auth:sanctum')->group(function () {
Route::post('/apartment/bookings', [BookingController::class, 'store']);
Route::get('/apartment/bookings', [BookingController::class, 'index']);

});

// Maintenance
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/maintenance/request', [MaintenanceController::class, 'submitRequest']);
    Route::get('/maintenance/my-requests', [MaintenanceController::class, 'myRequests']);
    Route::put('/maintenance/update-status', [MaintenanceController::class, 'updateStatus']);
});

// Ride Settings
Route::get('/ride/settings', [RideSettingController::class, 'index']);
Route::middleware('auth:sanctum')->put('/ride/settings', [RideSettingController::class, 'update']);


Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);


Route::post('/log-error', function (Request $request) {
    Log::error('Frontend Error', [
        'message' => $request->input('message'),
        'stack'   => $request->input('stack'),
        'context' => $request->input('context'),
        'url'     => $request->input('url'),
        'user_id' => auth('sanctum')->id() ?? null, // Optional if using auth
    ]);

    return response()->json(['status' => 'logged'], 200);
});