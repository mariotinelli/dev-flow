<?php

declare(strict_types = 1);

use App\Http\Controllers\ProjectRoles;
use App\Http\Controllers\Projects;
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

    Route::redirect('papeis-do-projeto', '/projeto/configuracoes/papeis');

    Route::redirect('projeto/configuracoes', '/projeto/configuracoes/papeis')->name('project-settings.index');
    Route::prefix('projeto/configuracoes')->name('project-settings.')->group(function () {
        Route::inertia('gitlab', 'project-settings/Gitlab')->name('gitlab');
        Route::inertia('loom', 'project-settings/Loom')->name('loom');
    });

    Route::prefix('projeto/configuracoes/papeis')->name('project-roles.')->group(function () {
        Route::get('/', ProjectRoles\IndexController::class)->name('index');
        Route::get('cadastrar', ProjectRoles\CreateController::class)->name('create');
        Route::post('copiar', ProjectRoles\CopyController::class)->name('copy');
        Route::post('/', ProjectRoles\StoreController::class)->name('store');
        Route::get('{projectRole}/editar', ProjectRoles\EditController::class)->name('edit');
        Route::post('{projectRole}', ProjectRoles\UpdateController::class)->name('update');
        Route::post('{projectRole}/ativar', ProjectRoles\ActivateController::class)->withTrashed()->name('activate');
        Route::delete('{projectRole}', ProjectRoles\DestroyController::class)->name('destroy');
    });

    Route::prefix('projetos')->name('projects.')->group(function () {
        Route::get('/', Projects\IndexController::class)->name('index');
        Route::get('cadastrar', Projects\CreateController::class)->name('create');
        Route::post('/', Projects\StoreController::class)->name('store');
        Route::get('{project}/editar', Projects\EditController::class)->name('edit');
        Route::post('{project}', Projects\UpdateController::class)->name('update');
        Route::post('{project}/ativar', Projects\ActivateController::class)->withTrashed()->name('activate');
        Route::post('{project}/selecionar', Projects\SelectController::class)->name('select');
        Route::delete('{project}', Projects\DestroyController::class)->name('destroy');
    });
});

require __DIR__ . '/settings.php';
