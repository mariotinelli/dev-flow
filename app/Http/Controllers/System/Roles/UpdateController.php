<?php

declare(strict_types = 1);

namespace App\Http\Controllers\System\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\Roles\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UpdateController extends Controller
{
    public function __invoke(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $validated = $request->validated();

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Perfil atualizado.']);

        return to_route('system.roles.index');
    }
}
