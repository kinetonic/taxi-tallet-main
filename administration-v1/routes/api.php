<?php

/*use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


/// Handle preflight OPTIONS requests for all API routes
Route::options('/{any}', function () {
    return response()->json([], 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
})->where('any', '.*');

// Public routes - No authentication required
Route::prefix('auth')->group(function () {
    // Registration
    Route::post('/register', [AuthController::class, 'register']);
    
    // Login
    Route::post('/login', [AuthController::class, 'login']);
    
    // Password reset (optional)
    Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);
});

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('auth')->group(function () {
        // Logout
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // Profile
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'updatePassword']);
    });
    
    // Driver-specific routes
    Route::prefix('driver')->middleware(['driver'])->group(function () {
        Route::post('/online-status', [AuthController::class, 'updateOnlineStatus']);
        Route::post('/location', [AuthController::class, 'updateLocation']);
    });
    
    // Rider-specific routes
    Route::prefix('rider')->middleware(['rider'])->group(function () {
        // Add rider-specific routes here
    });
});*/


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\DriverController;
//use App\Http\Controllers\Api\RiderController;
//use App\Http\Controllers\Api\PaymentController;
//use App\Http\Controllers\Api\WalletController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Map-related public endpoints
Route::prefix('map')->group(function () {
    Route::post('/calculate-fare', [MapController::class, 'calculateFareEstimate']);
    Route::post('/reverse-geocode', [MapController::class, 'reverseGeocode']);
    Route::get('/search-address', [MapController::class, 'searchAddress']);
});

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'updatePassword']);
    });
    
    // Map routes
    Route::prefix('map')->group(function () {
        Route::get('/nearby-drivers', [MapController::class, 'getNearbyDrivers']);
    });
    
    // Trip routes
    Route::prefix('trips')->group(function () {
        Route::post('/request', [TripController::class, 'requestTrip']);
        Route::get('/current', [TripController::class, 'getCurrentTrip']);
        Route::get('/history', [TripController::class, 'tripHistory']);
        
        Route::prefix('{trip}')->group(function () {
            Route::post('/accept', [TripController::class, 'acceptTrip'])->middleware(['driver']);
            Route::post('/start', [TripController::class, 'startTrip'])->middleware(['driver']);
            Route::post('/complete', [TripController::class, 'completeTrip'])->middleware(['driver']);
            Route::post('/cancel', [TripController::class, 'cancelTrip']);
            Route::post('/rate', [TripController::class, 'rateTrip']);
            Route::get('/driver-location', [TripController::class, 'getDriverLocation']);
        });
    });
    
    // Driver routes
    Route::prefix('driver')->middleware(['driver'])->group(function () {
        Route::get('/profile', [DriverController::class, 'getProfile']);
        Route::put('/profile', [DriverController::class, 'updateProfile']);
        Route::put('/status', [DriverController::class, 'updateStatus']);
        Route::post('/documents/upload', [DriverController::class, 'uploadDocument']);
        Route::get('/documents', [DriverController::class, 'getDocuments']);
        Route::get('/statistics', [DriverController::class, 'getStatistics']);
        Route::get('/earnings', [DriverController::class, 'getEarnings']);
        
        // Location updates
        Route::post('/location', [MapController::class, 'updateDriverLocation']);
    });
    
    // Rider routes
    /*Route::prefix('rider')->middleware(['rider'])->group(function () {
        Route::get('/payment-methods', [RiderController::class, 'getPaymentMethods']);
        Route::post('/payment-methods', [RiderController::class, 'addPaymentMethod']);
        Route::delete('/payment-methods/{paymentMethod}', [RiderController::class, 'removePaymentMethod']);
        Route::put('/payment-methods/{paymentMethod}/default', [RiderController::class, 'setDefaultPaymentMethod']);
    });*/
    
    // Wallet routes
    /*Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'getWallet']);
        Route::get('/transactions', [WalletController::class, 'getTransactions']);
        Route::post('/top-up', [WalletController::class, 'topUp']);
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->middleware(['driver']);
    });*/
    
    // Payment routes
    /*Route::prefix('payments')->group(function () {
        Route::post('/initiate', [PaymentController::class, 'initiatePayment']);
        Route::post('/verify', [PaymentController::class, 'verifyPayment']);
        Route::get('/history', [PaymentController::class, 'paymentHistory']);
    });*/
    
    // Notifications
    /*Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'getNotifications']);
        Route::put('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{notification}', [NotificationController::class, 'deleteNotification']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });*/
});