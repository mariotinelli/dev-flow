<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class StoreController extends Controller
{
    public function __invoke(StoreRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::create([
            'name'       => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Perfil cadastrado.']);

        return to_route('roles.index');
    }
}
