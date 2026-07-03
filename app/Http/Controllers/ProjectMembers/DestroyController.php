<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectMembers;

use App\Http\Controllers\Controller;
use App\Models\ProjectMember;
use App\Support\CurrentProject;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyController extends Controller
{
    public function __invoke(ProjectMember $projectMember): RedirectResponse
    {
        $this->authorize('delete', $projectMember);

        abort_unless($projectMember->project_id === CurrentProject::resolve(request())?->id, 404);

        $projectMember->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Membro removido do projeto.']);

        return to_route('project.members.index');
    }
}
