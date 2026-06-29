<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectRoles;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRoles\StoreProjectRoleRequest;
use App\Models\ProjectRole;
use App\Support\CurrentProject;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function __invoke(StoreProjectRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $project   = CurrentProject::resolve($request);

        abort_unless($project, 404);

        $projectRole = ProjectRole::create([
            'project_id' => $project->id,
            'name'       => $validated['name'],
        ]);

        $projectRole->syncPermissionNames($validated['permissions'] ?? []);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Papel do projeto cadastrado.']);

        return to_route('project-settings.roles.index');
    }
}
