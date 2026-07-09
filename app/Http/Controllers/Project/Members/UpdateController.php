<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Project\Members;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\Members\UpdateProjectMemberRequest;
use App\Models\ProjectMember;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UpdateController extends Controller
{
    public function __invoke(UpdateProjectMemberRequest $request, ProjectMember $projectMember): RedirectResponse
    {
        $projectMember->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Membro do projeto atualizado.']);

        return to_route('project.members.index');
    }
}
