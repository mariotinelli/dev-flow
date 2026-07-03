<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\CurrentProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SelectController extends Controller
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->hasRole('admin') || $user->isMemberOf($project), 403);

        $this->currentProject->select($project, $request);

        return back();
    }
}
