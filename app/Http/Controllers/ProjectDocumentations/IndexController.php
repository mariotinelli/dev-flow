<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectDocumentations;

use App\Enums\ProjectDocumentationCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectDocumentations\IndexProjectDocumentationRequest;
use App\Http\Resources\ProjectDocumentationResource;
use App\Models\ProjectDocumentation;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __invoke(IndexProjectDocumentationRequest $request): Response
    {
        $filters = $request->validated();

        $projectDocumentations = ProjectDocumentation::query()
            ->with('author')
            ->filters($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('project-documentations/Index', [
            'projectDocumentations' => ProjectDocumentationResource::collection($projectDocumentations),
            'can'                   => [
                'create' => $request->user()->can('create', ProjectDocumentation::class),
            ],
            'filters' => [
                'search' => $filters['search'] ?? '',
            ],
            'categories' => ProjectDocumentationCategory::options(),
        ]);
    }
}
