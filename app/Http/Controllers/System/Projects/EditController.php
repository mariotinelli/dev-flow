<?php

declare(strict_types = 1);

namespace App\Http\Controllers\System\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\Projects\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('system/projects/Edit', [
            'project' => (new ProjectResource($project))->resolve($request),
        ]);
    }
}
