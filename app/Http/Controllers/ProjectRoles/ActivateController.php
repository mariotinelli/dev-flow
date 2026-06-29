<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectRoles;

use App\Http\Controllers\Controller;
use App\Models\ProjectRole;
use App\Support\CurrentProject;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ActivateController extends Controller
{
    public function __invoke(ProjectRole $projectRole): RedirectResponse
    {
        $this->authorize('restore', $projectRole);

        abort_unless($projectRole->project_id === CurrentProject::resolve(request())?->id, 404);

        $projectRole->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Papel do projeto ativado.']);

        return to_route('project-settings.roles.index');
    }
}
