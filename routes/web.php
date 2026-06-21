<?php

declare(strict_types = 1);

use App\Http\Controllers\Roles;
use App\Http\Controllers\Users;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('usuarios')->name('users.')->group(function () {
        Route::get('/', Users\IndexController::class)->name('index');
        Route::get('cadastrar', Users\CreateController::class)->name('create');
        Route::post('/', Users\StoreController::class)->name('store');
        Route::get('{user}/editar', Users\EditController::class)->name('edit');
        Route::post('{user}', Users\UpdateController::class)->name('update');
        Route::post('{user}/ativar', Users\ActivateController::class)->withTrashed()->name('activate');
        Route::delete('{user}', Users\DestroyController::class)->name('destroy');
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
