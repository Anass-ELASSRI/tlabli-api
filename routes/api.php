<?php

use App\Http\Controllers\AccountVerification\PhoneVerificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ArtisanController;
use App\Http\Controllers\API\ArtisanRequestController;
use App\Http\Controllers\Auth\RefreshController;

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

Route::middleware(['silent.refresh'])->group(function () {
    // any other guest pages you want to silently redirect
    Route::post('/register-artisan', [AuthController::class, 'registerArtisan']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/artisans', [ArtisanController::class, 'index']);
    Route::get('/artisans/{id}', [ArtisanController::class, 'show']);
    Route::post('/login',    [AuthController::class, 'login']);
});



Route::get('/test', [ArtisanController::class, 'test']);

Route::middleware(['jwt'])->group(function () {
    Route::get('/me',    [AuthController::class, 'me']);
    Route::post('/auth/refresh', [RefreshController::class, 'refresh']);
    Route::get('/verify-phone/status', [PhoneVerificationController::class, 'status']);
    Route::post('/verify-phone', [PhoneVerificationController::class, 'verify']);
    Route::post('/resend-code', [PhoneVerificationController::class, 'resend']);


    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/complete-profile', [ArtisanController::class, 'completeRegistration']);
    // Route::get('/profile', [userC::class, 'profile']);

    Route::prefix('artisans')->group(function () {
        Route::put('/{id}', [ArtisanController::class, 'update']);
        // Route::get('/{id}/ratings', [RatingController::class, 'artisanRatings']);
        Route::post('/{artisan}/requests', [ArtisanRequestController::class, 'store']);
    });
});
