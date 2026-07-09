<?php

declare(strict_types = 1);

namespace App\Http\Controllers\System\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ActivateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Project $project): RedirectResponse
    {
        $this->authorize('restore', $project);

        $project->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Projeto ativado.']);

        return to_route('system.projects.index');
    }
}
