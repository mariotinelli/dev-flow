<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectMembers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectMembers\StoreProjectMemberRequest;
use App\Models\ProjectMember;
use App\Support\CurrentProject;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    public function __invoke(StoreProjectMemberRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $project   = $this->currentProject->resolve($request);

        abort_unless($project, 404);

        ProjectMember::query()->create([
            'project_id'      => $project->id,
            'user_id'         => $validated['user_id'],
            'project_role_id' => $validated['project_role_id'],
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Membro do projeto cadastrado.']);

        return to_route('project.members.index');
    }
}
