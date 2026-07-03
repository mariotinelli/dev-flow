<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectSettings\ProjectRoles;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRoles\UpdateProjectRoleRequest;
use App\Models\ProjectRole;
use App\Support\CurrentProject;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UpdateController extends Controller
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    public function __invoke(UpdateProjectRoleRequest $request, ProjectRole $projectRole): RedirectResponse
    {
        $validated = $request->validated();

        abort_unless($projectRole->project_id === $this->currentProject->resolve($request)?->id, 404);

        $projectRole->update([
            'name' => $validated['name'],
        ]);

        $projectRole->syncPermissionNames($validated['permissions'] ?? []);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Papel do projeto atualizado.']);

        return to_route('project-settings.roles.index');
    }
}
