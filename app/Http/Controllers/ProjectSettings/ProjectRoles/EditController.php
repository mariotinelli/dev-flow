<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectSettings\ProjectRoles;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\ProjectRole;
use App\Support\CurrentProject;
use Inertia\Inertia;
use Inertia\Response;

class EditController extends Controller
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    public function __invoke(ProjectRole $projectRole): Response
    {
        $this->authorize('update', $projectRole);

        abort_unless($projectRole->project_id === $this->currentProject->resolve()?->id, 404);

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
