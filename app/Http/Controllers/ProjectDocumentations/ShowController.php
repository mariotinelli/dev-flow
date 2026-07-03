<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectDocumentations;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectDocumentationResource;
use App\Models\ProjectDocumentation;
use Inertia\Inertia;
use Inertia\Response;

class ShowController extends Controller
{
    public function __invoke(ProjectDocumentation $projectDocumentation): Response
    {
        $this->authorize('view', $projectDocumentation);

        $projectDocumentation->load('author');

        return Inertia::render('project-documentations/Show', [
            'projectDocumentation' => ProjectDocumentationResource::make($projectDocumentation)->resolve(request()),
        ]);
    }
}
