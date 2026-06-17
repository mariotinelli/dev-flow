<?php

declare(strict_types = 1);

use App\Http\Controllers\Developers\ActivateController;
use App\Http\Controllers\Developers\CreateController;
use App\Http\Controllers\Developers\DestroyController;
use App\Http\Controllers\Developers\EditController;
use App\Http\Controllers\Developers\IndexController;
use App\Http\Controllers\Developers\StoreController;
use App\Http\Controllers\Developers\UpdateController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('developers')->name('developers.')->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('create', CreateController::class)->name('create');
        Route::post('/', StoreController::class)->name('store');
        Route::get('{developer}/edit', EditController::class)->name('edit');
        Route::post('{developer}', UpdateController::class)->name('update');
        Route::post('{developer}/activate', ActivateController::class)->withTrashed()->name('activate');
        Route::delete('{developer}', DestroyController::class)->name('destroy');
    });
});

require __DIR__ . '/settings.php';
