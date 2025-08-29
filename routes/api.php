<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ArtisanController;
use App\Http\Controllers\API\ArtisanRequestController;
use Illuminate\Support\Facades\Artisan;

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



Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    return 'Cache cleared!';
});

Route::get('/debug', function (\Illuminate\Http\Request $request) {
    return [
        'is_secure' => $request->isSecure(),
        'scheme' => $request->getScheme(),
        'headers' => $request->headers->all(),
    ];
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/artisans', [ArtisanController::class, 'index']);
Route::get('/artisans/{id}', [  ArtisanController::class, 'show']); 

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',    [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/complete-registration', [ArtisanController::class, 'completeRegistration']);
    // Route::get('/profile', [userC::class, 'profile']);
    
    Route::prefix('artisans')->group(function () {
        Route::put('/{id}', [ArtisanController::class, 'update']);
        // Route::get('/{id}/ratings', [RatingController::class, 'artisanRatings']);
        Route::post('/{artisan}/requests', [ArtisanRequestController::class, 'store']);

    });
});
