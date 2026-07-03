<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectSettings\ProjectRoles;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRoles\UpdateProjectRoleRequest;
use App\Models\ProjectRole;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UpdateController extends Controller
{
    public function __invoke(UpdateProjectRoleRequest $request, ProjectRole $projectRole): RedirectResponse
    {
        $validated = $request->validated();

        $projectRole->update([
            'name' => $validated['name'],
        ]);

        $projectRole->syncPermissionNames($validated['permissions'] ?? []);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Papel do projeto atualizado.']);

        return to_route('project-settings.roles.index');
    }
}
