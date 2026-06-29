<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectRoles;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRoles\CopyProjectRolesRequest;
use App\Models\Project;
use App\Models\ProjectRole;
use App\Support\CurrentProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CopyController extends Controller
{
    public function __invoke(CopyProjectRolesRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $project   = CurrentProject::resolve($request);

        abort_unless($project, 404);

        $sourceProject = Project::query()
            ->with(['projectRoles.permissions'])
            ->findOrFail($validated['source_project_id']);

        DB::transaction(function () use ($project, $sourceProject): void {
            foreach ($sourceProject->projectRoles as $sourceRole) {
                $projectRole = ProjectRole::withTrashed()->firstOrNew([
                    'project_id' => $project->id,
                    'name'       => $sourceRole->name,
                ]);

                $projectRole->save();

                if ($projectRole->trashed()) {
                    $projectRole->restore();
                }

                $projectRole->permissions()->sync($sourceRole->permissions->modelKeys());
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Papéis copiados para o projeto atual.']);

        return to_route('project-roles.index');
    }
}
