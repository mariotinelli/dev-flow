<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Roles;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class EditController extends Controller
{
    public function __invoke(Role $role): Response
    {
        $this->authorize('update', $role);

        $role->load('permissions');

        return Inertia::render('roles/Edit', [
            'role' => [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $role->permissions->pluck('name')->values(),
            ],
            'permissionGroups' => Permission::groupedOptions(),
        ]);
    }
}
