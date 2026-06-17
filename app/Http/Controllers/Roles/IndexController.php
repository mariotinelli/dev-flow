<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class IndexController extends Controller
{
    public function __invoke(): Response
    {
        $this->authorize('viewAny', Role::class);

        return Inertia::render('roles/Index', [
            'roles' => Role::query()
                ->withCount('permissions')
                ->withCount('users')
                ->orderByRaw("name = 'admin' desc")
                ->orderBy('name')
                ->get()
                ->map(fn (Role $role): array => [
                    'id'                => $role->id,
                    'name'              => $role->name,
                    'permissions_count' => $role->permissions_count,
                    'users_count'       => $role->users_count,
                    'is_protected'      => $role->name === 'admin',
                    'is_in_use'         => $role->users_count > 0,
                    'can'               => [
                        'update' => Gate::allows('update', $role),
                        'delete' => Gate::allows('delete', $role) && $role->users_count === 0,
                    ],
                ]),
            'can' => [
                'create' => Gate::allows('create', Role::class),
            ],
        ]);
    }
}
