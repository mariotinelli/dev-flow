<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectMembers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectMembers\UpdateProjectMemberRequest;
use App\Models\ProjectMember;
use App\Support\CurrentProject;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UpdateController extends Controller
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    public function __invoke(UpdateProjectMemberRequest $request, ProjectMember $projectMember): RedirectResponse
    {
        $validated = $request->validated();

        abort_unless($projectMember->project_id === $this->currentProject->resolve($request)?->id, 404);

        $projectMember->update([
            'user_id'         => $validated['user_id'],
            'project_role_id' => $validated['project_role_id'],
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Membro do projeto atualizado.']);

        return to_route('project.members.index');
    }
}
