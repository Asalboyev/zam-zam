<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DeliversController;

use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\DailyMealController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\RegionController;
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

//Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function(){
//    Route::get('dashboard', [CustomersController::class, 'dashboard'])->name('dashboard');
////    Route::get('dashboard', [\App\Http\Controllers\MainControler::class, 'stats'])->name('dashboard');
//
//    Route::get('/orders/indebted-customers', [CustomersController::class, 'indebted_customers'])->name('indebted_customers');
//
//    Route::resource('customers', CustomersController::class);
//    Route::resource('products', ProductsController::class);
//    Route::resource('daily_meal', DailyMealController::class);
//    Route::post('/orders/{order}/update-received-amount', [OrdersController::class, 'updateReceivedAmount']);
//
//    Route::put('/daily_meal/item-update/{id}', [DailyMealController::class, 'itemUpdate'])->name('daily_meal.item_update');
//    Route::get('/cusomers/balance-Histories/{id}', [CustomersController::class, 'Histories'])->name('cusomers.histories');
//
//
//    Route::get('/regions', [RegionController::class, 'index'])->name('regions.index');
//    Route::get('/regions/create', [RegionController::class, 'create'])->name('regions.create');
//    Route::post('/regions', [RegionController::class, 'store'])->name('regions.store');
//    Route::get('/regions/{id}/edit', [RegionController::class, 'edit'])->name('regions.edit');
//    Route::put('/regions/{id}', [RegionController::class, 'update'])->name('regions.update');
//    Route::delete('/regions/{id}', [RegionController::class, 'destroy'])->name('regions.destroy');
//
//
//    Route::resource('drivers', DeliversController::class);
//    Route::get('/orders/ordinary-debt', [OrdersController::class, 'ordinary_debt'])->name('ordinary_debt');
//    Route::get('/orders/monthly-debtors', [OrdersController::class, 'monthly_debtors'])->name('monthly_debtors');
//
//    Route::post('/admin/orders/{order}/update-payment-method', [OrdersController::class, 'updatePaymentMethod']);
//
//    Route::resource('orders', OrdersController::class);
//    Route::get('/orders/all', [OrdersController::class, 'show'])->name('orders.all');
//
//
//    Route::get('/admin/get-meals-by-date', [OrdersController::class, 'getMealsByDate'])->name('admin.getMealsByDate');
//    Route::post('/upload-image', [ProductsController::class, 'image_upload'])->name('image.upload');
//    Route::post('/admin/image/delete', [ProductsController::class, 'image_delete'])->name('image.delete');
//});


Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function(){

    // 🔹 Dashboard faqat admin ko‘radi
    Route::get('dashboard', [CustomersController::class, 'dashboard'])
        ->middleware('role:admin')
        ->name('dashboard');

    // 🔹 Qolgan hamma narsani admin ham, seller ham ko‘ra oladi
    Route::get('/orders/indebted-customers', [CustomersController::class, 'indebted_customers'])->name('indebted_customers');

    Route::resource('customers', CustomersController::class);
    Route::resource('products', ProductsController::class);
    Route::resource('daily_meal', DailyMealController::class)->only([
        'index', 'create','store','edit','update', 'destroy'
    ]);
    Route::post('/orders/{order}/update-received-amount', [OrdersController::class, 'updateReceivedAmount']);

    Route::put('/daily_meal/item-update/{id}', [DailyMealController::class, 'itemUpdate'])->name('daily_meal.item_update');
    Route::get('/cusomers/balance-Histories/{id}', [CustomersController::class, 'Histories'])->name('cusomers.histories');

    Route::get('/regions', [RegionController::class, 'index'])->name('regions.index');
    Route::get('/regions/create', [RegionController::class, 'create'])->name('regions.create');
    Route::post('/regions', [RegionController::class, 'store'])->name('regions.store');
    Route::get('/regions/{id}/edit', [RegionController::class, 'edit'])->name('regions.edit');
    Route::put('/regions/{id}', [RegionController::class, 'update'])->name('regions.update');
    Route::delete('/regions/{id}', [RegionController::class, 'destroy'])->name('regions.destroy');

    Route::resource('drivers', DeliversController::class);
    Route::get('/orders/ordinary-debt', [OrdersController::class, 'ordinary_debt'])->name('ordinary_debt');
    Route::get('/orders/monthly-debtors', [OrdersController::class, 'monthly_debtors'])->name('monthly_debtors');

    Route::post('/admin/orders/{order}/update-payment-method', [OrdersController::class, 'updatePaymentMethod']);

    Route::resource('orders', OrdersController::class);
    Route::get('/orders/all', [OrdersController::class, 'show'])->name('orders.all');

    Route::get('/admin/get-meals-by-date', [OrdersController::class, 'getMealsByDate'])->name('admin.getMealsByDate');
    Route::post('/upload-image', [ProductsController::class, 'image_upload'])->name('image.upload');
    Route::post('/admin/image/delete', [ProductsController::class, 'image_delete'])->name('image.delete');
});




require __DIR__.'/auth.php';
