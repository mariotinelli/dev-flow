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
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $user->hasRole('admin') || $project->members()->where('user_id', $user->id)->exists(),
            403,
        );

        CurrentProject::select($request, $project);

        return back();
    }
}
