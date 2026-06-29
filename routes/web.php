<?php

use App\Http\Controllers\Admin\AdminChecklistItemController;
use App\Http\Controllers\Admin\AnalysisController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderHistoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', fn () => view('auth.login', [
    'title' => 'Rya Bakery Admin | Login',
]))->name('login');

Route::redirect('/backend/admin', '/dashboard');

Route::middleware(['auth', 'verified'])->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/dashboard/checklist', [AdminChecklistItemController::class, 'store'])->middleware('throttle:admin-actions')->name('checklist.store');
    Route::patch('/dashboard/checklist/{checklistItem}', [AdminChecklistItemController::class, 'update'])->middleware('throttle:admin-actions')->name('checklist.update');
    Route::delete('/dashboard/checklist/{checklistItem}', [AdminChecklistItemController::class, 'destroy'])->middleware('throttle:admin-actions')->name('checklist.destroy');
    Route::get('/analysis', AnalysisController::class)->name('analysis.index');
    Route::get('products/{product:slug}/scheda', [ProductController::class, 'edit'])->name('products.edit');
    Route::resource('products', ProductController::class)->except(['show', 'edit']);
    Route::get('orders/live', [OrderController::class, 'liveIndex'])->middleware('throttle:admin-actions')->name('orders.live');
    Route::get('orders/{order:slug}/scheda', [OrderController::class, 'edit'])->name('orders.edit');
    Route::resource('orders', OrderController::class)->except(['create', 'store', 'show', 'edit']);
    Route::patch('orders/{order:slug}/accept', [OrderController::class, 'accept'])->middleware('throttle:admin-actions')->name('orders.accept');
    Route::patch('orders/{order:slug}/cancel', [OrderController::class, 'cancel'])->middleware('throttle:admin-actions')->name('orders.cancel');
    Route::patch('orders/{order:slug}/complete', [OrderController::class, 'complete'])->middleware('throttle:admin-actions')->name('orders.complete');
    Route::get('order-history', [OrderHistoryController::class, 'index'])->name('order-history.index');
    Route::get('order-history/export', [OrderHistoryController::class, 'export'])->middleware('throttle:admin-exports')->name('order-history.export');
    Route::get('order-history/report', [OrderHistoryController::class, 'report'])->middleware('throttle:admin-exports')->name('order-history.report');
    Route::patch('order-history/{order:slug}/restore', [OrderHistoryController::class, 'restore'])->middleware('throttle:admin-actions')->name('order-history.restore');

    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [ProfileController::class, 'update'])->name('settings.update');
    Route::delete('/settings', [ProfileController::class, 'destroy'])->name('settings.destroy');
});

Route::redirect('/profile', '/settings');

require __DIR__.'/auth.php';
