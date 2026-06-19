<?php

use App\Http\Controllers\PublicApi\OrderController;
use App\Http\Controllers\PublicApi\ProductCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductCatalogController::class, 'index'])->middleware('throttle:catalog')->name('api.products.index');
Route::get('/products/most-ordered', [ProductCatalogController::class, 'mostOrdered'])->middleware('throttle:catalog')->name('api.products.most-ordered');
Route::get('/products/{product:slug}', [ProductCatalogController::class, 'show'])->middleware('throttle:catalog')->name('api.products.show');
Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:public-orders')->name('api.orders.store');
Route::get('/orders/{order:slug}', [OrderController::class, 'show'])->middleware('throttle:order-status')->name('api.orders.show');
