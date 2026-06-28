<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Projeto inativado.']);

        return to_route('projects.index');
    }
}
