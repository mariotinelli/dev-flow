<?php

declare(strict_types = 1);

use App\Http\Controllers\ProjectAiChat;
use App\Http\Controllers\ProjectDocumentations;
use App\Http\Controllers\ProjectMembers;
use App\Http\Controllers\Projects;
use App\Http\Controllers\ProjectSettings;
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

    Route::prefix('configuracoes-do-projeto')->name('project-settings.')->middleware('project.scope')->group(function () {
        Route::inertia('gitlab', 'project-settings/Gitlab')->name('gitlab');
        Route::inertia('loom', 'project-settings/Loom')->name('loom');

        Route::prefix('papeis')->name('roles.')->group(function () {
            Route::get('/', ProjectSettings\ProjectRoles\IndexController::class)->name('index');
            Route::get('cadastrar', ProjectSettings\ProjectRoles\CreateController::class)->name('create');
            Route::post('copiar', ProjectSettings\ProjectRoles\CopyController::class)->name('copy');
            Route::post('/', ProjectSettings\ProjectRoles\StoreController::class)->name('store');
            Route::get('{projectRole}/editar', ProjectSettings\ProjectRoles\EditController::class)->name('edit');
            Route::post('{projectRole}', ProjectSettings\ProjectRoles\UpdateController::class)->name('update');
            Route::post('{projectRole}/ativar', ProjectSettings\ProjectRoles\ActivateController::class)->withTrashed()->name('activate');
            Route::delete('{projectRole}', ProjectSettings\ProjectRoles\DestroyController::class)->name('destroy');
        });

    });

    Route::prefix('projeto')->name('project.')->middleware('project.scope')->group(function () {
        Route::prefix('chat-ia')->name('ai-chat.')->group(function () {
            Route::get('/', ProjectAiChat\IndexController::class)->name('index');
            Route::post('stream', ProjectAiChat\StreamController::class)->name('stream');
        });

        Route::prefix('membros')->name('members.')->group(function () {
            Route::get('/', ProjectMembers\IndexController::class)->name('index');
            Route::post('/', ProjectMembers\StoreController::class)->name('store');
            Route::post('{projectMember}', ProjectMembers\UpdateController::class)->name('update');
            Route::delete('{projectMember}', ProjectMembers\DestroyController::class)->name('destroy');
        });

        Route::prefix('documentacoes')->name('documentations.')->group(function () {
            Route::get('/', ProjectDocumentations\IndexController::class)->name('index');
            Route::post('/', ProjectDocumentations\StoreController::class)->name('store');
            Route::get('{projectDocumentation}/download', ProjectDocumentations\DownloadController::class)->name('download');
            Route::post('{projectDocumentation}', ProjectDocumentations\UpdateController::class)->name('update');
            Route::delete('{projectDocumentation}', ProjectDocumentations\DestroyController::class)->name('destroy');
        });
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
