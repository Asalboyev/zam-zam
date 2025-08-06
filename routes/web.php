<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DeliversController;

use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\DailyMealController;
use App\Http\Controllers\Admin\OrdersController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/





Route::get('/', function ()
{
    return redirect('/login');
});;

Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function(){
    Route::get('dashboard', [CustomersController::class, 'dashboard'])->name('dashboard');
    Route::resource('customers', CustomersController::class);
    Route::resource('products', ProductsController::class);
    Route::resource('daily_meal', DailyMealController::class);

    Route::put('/daily_meal/item-update/{id}', [DailyMealController::class, 'itemUpdate'])->name('daily_meal.item_update');


    Route::resource('drivers', DeliversController::class);
    Route::resource('orders', OrdersController::class);
    Route::get('/orders/all', [OrdersController::class, 'show'])->name('orders.all');

    Route::get('/admin/get-meals-by-date', [OrdersController::class, 'getMealsByDate'])->name('admin.getMealsByDate');
    Route::post('/upload-image', [ProductsController::class, 'image_upload'])->name('image.upload');
    Route::post('/admin/image/delete', [ProductsController::class, 'image_delete'])->name('image.delete');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
