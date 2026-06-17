<?php

use App\Http\Controllers\PublicApi\OrderController;
use App\Http\Controllers\PublicApi\ProductCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductCatalogController::class, 'index'])->name('api.products.index');
Route::get('/products/{product:slug}', [ProductCatalogController::class, 'show'])->name('api.products.show');
Route::post('/orders', [OrderController::class, 'store'])->name('api.orders.store');
Route::get('/orders/{order:slug}', [OrderController::class, 'show'])->name('api.orders.show');
