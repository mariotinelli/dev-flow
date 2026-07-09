<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Project\Settings\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\Settings\Roles\CopyProjectRolesRequest;
use App\Models\Project;
use App\Models\ProjectRole;
use App\Models\Scopes\BelongsToCurrentProjectScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CopyController extends Controller
{
    public function __invoke(CopyProjectRolesRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $sourceProject = Project::query()
            ->with([
                'projectRoles' => fn ($query) => $query->withoutGlobalScope(BelongsToCurrentProjectScope::class)->withTrashed(),
                'projectRoles.permissions',
            ])
            ->findOrFail($validated['source_project_id']);

        DB::transaction(function () use ($sourceProject): void {
            foreach ($sourceProject->projectRoles as $sourceRole) {
                $projectRole = ProjectRole::withTrashed()->firstOrNew([
                    'name' => $sourceRole->name,
                ]);

                $projectRole->save();

                if ($projectRole->trashed()) {
                    $projectRole->restore();
                }

                $projectRole->permissions()->sync($sourceRole->permissions->modelKeys());
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Papéis copiados para o projeto atual.']);

        return to_route('project.settings.roles.index');
    }
}
