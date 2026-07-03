<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectSettings\ProjectRoles;

use App\Http\Controllers\Controller;
use App\Models\ProjectRole;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyController extends Controller
{
    public function __invoke(ProjectRole $projectRole): RedirectResponse
    {
        $this->authorize('delete', $projectRole);

        $projectRole->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Papel do projeto inativado.']);

        return to_route('project-settings.roles.index');
    }
}
