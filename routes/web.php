<?php

declare(strict_types = 1);

use App\Enums\Permissions\Project\Settings\GitlabPermissions;
use App\Enums\Permissions\Project\Settings\LoomPermissions;
use App\Http\Controllers\Intelligence;
use App\Http\Controllers\Project;
use App\Http\Controllers\System;
use App\Models\User;
use App\Support\CurrentProject;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('sistema')->name('system.')->group(function () {
        Route::prefix('usuarios')->name('users.')->group(function () {
            Route::get('/', System\Users\IndexController::class)->name('index');
            Route::get('cadastrar', System\Users\CreateController::class)->name('create');
            Route::post('/', System\Users\StoreController::class)->name('store');
            Route::get('{user}/editar', System\Users\EditController::class)->name('edit');
            Route::post('{user}', System\Users\UpdateController::class)->name('update');
            Route::post('{user}/ativar', System\Users\ActivateController::class)->withTrashed()->name('activate');
            Route::delete('{user}', System\Users\DestroyController::class)->name('destroy');
        });

        Route::prefix('perfis')->name('roles.')->group(function () {
            Route::get('/', System\Roles\IndexController::class)->name('index');
            Route::get('cadastrar', System\Roles\CreateController::class)->name('create');
            Route::post('/', System\Roles\StoreController::class)->name('store');
            Route::get('{role}/editar', System\Roles\EditController::class)->name('edit');
            Route::post('{role}', System\Roles\UpdateController::class)->name('update');
            Route::delete('{role}', System\Roles\DestroyController::class)->name('destroy');
        });

        Route::prefix('projetos')->name('projects.')->group(function () {
            Route::get('/', System\Projects\IndexController::class)->name('index');
            Route::get('cadastrar', System\Projects\CreateController::class)->name('create');
            Route::post('/', System\Projects\StoreController::class)->name('store');
            Route::get('{project}/editar', System\Projects\EditController::class)->name('edit');
            Route::post('{project}', System\Projects\UpdateController::class)->name('update');
            Route::post('{project}/ativar', System\Projects\ActivateController::class)->withTrashed()->name('activate');
            Route::post('{project}/selecionar', System\Projects\SelectController::class)->name('select');
            Route::delete('{project}', System\Projects\DestroyController::class)->name('destroy');
        });
    });

    Route::prefix('inteligencia')->name('intelligence.')->middleware('project.scope')->group(function () {
        Route::prefix('chat-ia')->name('ai-chat.')->group(function () {
            Route::get('/', Intelligence\AiChat\IndexController::class)->name('index');
            Route::post('stream', Intelligence\AiChat\StreamController::class)->name('stream');
        });
    });

    Route::prefix('projeto')->name('project.')->middleware('project.scope')->group(function () {
        Route::prefix('configuracoes')->name('settings.')->middleware('project.scope')->group(function () {
            Route::get('gitlab', function (CurrentProject $currentProject) {
                $user = request()->user();

                abort_unless($user instanceof User && ($user->hasRole('admin') || $currentProject->hasPermission(GitlabPermissions::View->value)), 403);

                return inertia('project/settings/Gitlab');
            })->name('gitlab');

            Route::get('loom', function (CurrentProject $currentProject) {
                $user = request()->user();

                abort_unless($user instanceof User && ($user->hasRole('admin') || $currentProject->hasPermission(LoomPermissions::View->value)), 403);

                return inertia('project/settings/Loom');
            })->name('loom');

            Route::prefix('papeis')->name('roles.')->group(function () {
                Route::get('/', Project\Settings\Roles\IndexController::class)->name('index');
                Route::get('cadastrar', Project\Settings\Roles\CreateController::class)->name('create');
                Route::post('copiar', Project\Settings\Roles\CopyController::class)->name('copy');
                Route::post('/', Project\Settings\Roles\StoreController::class)->name('store');
                Route::get('{projectRole}/editar', Project\Settings\Roles\EditController::class)->name('edit');
                Route::post('{projectRole}', Project\Settings\Roles\UpdateController::class)->name('update');
                Route::post('{projectRole}/ativar', Project\Settings\Roles\ActivateController::class)->withTrashed()->name('activate');
                Route::delete('{projectRole}', Project\Settings\Roles\DestroyController::class)->name('destroy');
            });
        });

        Route::prefix('membros')->name('members.')->group(function () {
            Route::get('/', Project\Members\IndexController::class)->name('index');
            Route::post('/', Project\Members\StoreController::class)->name('store');
            Route::post('{projectMember}', Project\Members\UpdateController::class)->name('update');
            Route::delete('{projectMember}', Project\Members\DestroyController::class)->name('destroy');
        });

        Route::prefix('documentacoes')->name('documentations.')->group(function () {
            Route::get('/', Project\Documentations\IndexController::class)->name('index');
            Route::post('/', Project\Documentations\StoreController::class)->name('store');
            Route::get('{projectDocumentation}/download', Project\Documentations\DownloadController::class)->name('download');
            Route::post('{projectDocumentation}', Project\Documentations\UpdateController::class)->name('update');
            Route::delete('{projectDocumentation}', Project\Documentations\DestroyController::class)->name('destroy');
        });
    });

});

require __DIR__ . '/settings.php';
