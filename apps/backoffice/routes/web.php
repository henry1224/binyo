<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OperationalController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/operational')->name('dashboard');
    Route::get('catalog', CatalogController::class)->name('catalog.index');
    Route::post('catalog/categories', [CatalogController::class, 'storeCategory'])
        ->name('catalog.categories.store');
    Route::put('catalog/categories/{category}', [CatalogController::class, 'updateCategory'])
        ->whereNumber('category')
        ->name('catalog.categories.update');
    Route::post('catalog/products', [CatalogController::class, 'storeProduct'])
        ->name('catalog.products.store');
    Route::put('catalog/products/{product}', [CatalogController::class, 'updateProduct'])
        ->whereNumber('product')
        ->name('catalog.products.update');
    Route::get('inventory', InventoryController::class)->name('inventory.index');
    Route::post('inventory/opening-stock', [InventoryController::class, 'storeOpeningStock'])
        ->name('inventory.opening-stock.store');
    Route::get('operational', OperationalController::class)->name('operational.index');
    Route::post('operational/outlets', [OperationalController::class, 'store'])
        ->name('operational.outlets.store');
    Route::put('operational/outlets/{outlet}', [OperationalController::class, 'update'])
        ->whereNumber('outlet')
        ->name('operational.outlets.update');
});

require __DIR__.'/settings.php';
