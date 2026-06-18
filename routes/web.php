<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderHistoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', fn () => view('auth.login', [
    'title' => 'Rya Bakery Admin | Login',
]));

Route::get('/backend/admin', fn () => Auth::check()
    ? redirect()->route('admin.dashboard')
    : view('auth.login', ['title' => 'Rya Bakery Admin | Login'])
)->name('login');

Route::middleware(['auth', 'verified'])->prefix('backend/admin')->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('orders', OrderController::class)->except(['create', 'store', 'show']);
    Route::patch('orders/{order:slug}/accept', [OrderController::class, 'accept'])->name('orders.accept');
    Route::patch('orders/{order:slug}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('orders/{order:slug}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::get('order-history', [OrderHistoryController::class, 'index'])->name('order-history.index');
    Route::get('order-history/export', [OrderHistoryController::class, 'export'])->name('order-history.export');
    Route::patch('order-history/{order:slug}/restore', [OrderHistoryController::class, 'restore'])->name('order-history.restore');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
