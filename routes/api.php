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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/daily-meals', [App\Http\Controllers\Api\DailyMealController::class, 'index']);
Route::post('/customers', [App\Http\Controllers\Api\DailyMealController::class, 'store']);
Route::post('/order', [App\Http\Controllers\Api\DailyMealController::class, 'order']);


