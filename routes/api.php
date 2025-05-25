<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\CraftmanController;

Route::post('/register/client', [AuthController::class, 'registerClient']);
Route::post('/register/craftman', [AuthController::class, 'registerCraftsman']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::get('/profile', [userC::class, 'profile']);

    Route::prefix('creaftmen')->group(function () {
        Route::get('/index', [CraftmanController::class, 'index']);
        Route::get('/{id}', [CraftmanController::class, 'show']);
        Route::put('/{id}', [CraftmanController::class, 'update']);
        // Route::get('/{id}/ratings', [RatingController::class, 'craftmanRatings']); // GET /craftman/{id}/ratings
    });
});
