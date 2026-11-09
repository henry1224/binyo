<?php

use App\Http\Controllers\OperationalController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/operational')->name('dashboard');
    Route::get('operational', OperationalController::class)->name('operational.index');
    Route::post('operational/outlets', [OperationalController::class, 'store'])
        ->name('operational.outlets.store');
    Route::put('operational/outlets/{outlet}', [OperationalController::class, 'update'])
        ->whereNumber('outlet')
        ->name('operational.outlets.update');
});

require __DIR__.'/settings.php';
