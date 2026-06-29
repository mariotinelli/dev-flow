<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectRoles;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\ProjectRole;
use App\Support\CurrentProject;
use Inertia\Inertia;
use Inertia\Response;

class EditController extends Controller
{
    public function __invoke(ProjectRole $projectRole): Response
    {
        $this->authorize('update', $projectRole);

        abort_unless($projectRole->project_id === CurrentProject::resolve(request())?->id, 404);

        $projectRole->load('permissions');

        return Inertia::render('project-roles/Edit', [
            'projectRole' => [
                'id'          => $projectRole->id,
                'name'        => $projectRole->name,
                'permissions' => $projectRole->permissions->pluck('name')->values(),
            ],
            'permissionGroups' => Permission::projectGroupedOptions(),
        ]);
    }
}
