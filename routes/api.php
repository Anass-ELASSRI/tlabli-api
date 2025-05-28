<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CraftsmanController;
use App\Http\Controllers\API\CraftsmanRequestController;

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


Route::post('/register', [AuthController::class, 'register']);

Route::post('/login',    [AuthController::class, 'login']);
Route::get('/craftsmen/index', [CraftsmanController::class, 'index']);
Route::get('/craftsmen/{id}', [  CraftsmanController::class, 'show']); 

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user',    [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::get('/profile', [userC::class, 'profile']);
    
    Route::prefix('craftsmen')->group(function () {
        Route::put('/{id}', [CraftsmanController::class, 'update']);
        // Route::get('/{id}/ratings', [RatingController::class, 'craftsmanRatings']);
        Route::post('/complete-registration', [CraftsmanController::class, 'completeRegistration']);
        Route::post('/{craftsman}/requests', [CraftsmanRequestController::class, 'store']);

    });
});
