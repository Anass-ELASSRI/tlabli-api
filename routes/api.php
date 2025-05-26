<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CraftmanController;

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
Route::get('/craftsman/index', [CraftmanController::class, 'index']);
Route::get('/craftsman/{id}', [  CraftmanController::class, 'show']); 

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::get('/profile', [userC::class, 'profile']);
    
    Route::prefix('craftsman')->group(function () {
        Route::put('/{id}', [CraftmanController::class, 'update']);
        // Route::get('/{id}/ratings', [RatingController::class, 'craftmanRatings']);
        Route::post('/complete-registration', [CraftmanController::class, 'completeRegistration']);
    });
});
