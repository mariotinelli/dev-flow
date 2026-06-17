<?php

declare(strict_types = 1);

use App\Http\Controllers\Developers;
use App\Http\Controllers\Roles;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('desenvolvedores')->name('developers.')->group(function () {
        Route::get('/', Developers\IndexController::class)->name('index');
        Route::get('cadastrar', Developers\CreateController::class)->name('create');
        Route::post('/', Developers\StoreController::class)->name('store');
        Route::get('{developer}/editar', Developers\EditController::class)->name('edit');
        Route::post('{developer}', Developers\UpdateController::class)->name('update');
        Route::post('{developer}/ativar', Developers\ActivateController::class)->withTrashed()->name('activate');
        Route::delete('{developer}', Developers\DestroyController::class)->name('destroy');
    });

    Route::prefix('perfis')->name('roles.')->group(function () {
        Route::get('/', Roles\IndexController::class)->name('index');
        Route::get('cadastrar', Roles\CreateController::class)->name('create');
        Route::post('/', Roles\StoreController::class)->name('store');
        Route::get('{role}/editar', Roles\EditController::class)->name('edit');
        Route::post('{role}', Roles\UpdateController::class)->name('update');
        Route::delete('{role}', Roles\DestroyController::class)->name('destroy');
    });
});

require __DIR__ . '/settings.php';
