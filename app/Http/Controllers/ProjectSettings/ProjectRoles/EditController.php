<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectSettings\ProjectRoles;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\ProjectRole;
use Inertia\Inertia;
use Inertia\Response;

class EditController extends Controller
{
    public function __invoke(ProjectRole $projectRole): Response
    {
        $this->authorize('update', $projectRole);

        $projectRole->load('permissions');

        return Inertia::render('project-settings/roles/Edit', [
            'projectRole' => [
                'id'          => $projectRole->id,
                'name'        => $projectRole->name,
                'permissions' => $projectRole->permissions->pluck('name')->values(),
            ],
            'permissionGroups' => Permission::projectGroupedOptions(),
        ]);
    }
}
