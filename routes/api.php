<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DailyMealController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/daily-meals', [DailyMealController::class, 'index']);
Route::get('/customers', [DailyMealController::class, 'get']);
Route::get('/customers/telegram/{telegram}', [DailyMealController::class, 'getByTelegram']);
Route::post('/customers', [DailyMealController::class, 'store']);
Route::post('/orders', [DailyMealController::class, 'order']);


//8316730787:AAHToGm0wxZK7QSaG6DdhjWD_tycgvfHbqM
