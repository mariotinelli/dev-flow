<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectDocumentations;

use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectDocumentations\IndexProjectDocumentationRequest;
use App\Http\Resources\ProjectDocumentationResource;
use App\Models\ProjectDocumentation;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __invoke(IndexProjectDocumentationRequest $request): Response
    {
        $filters = $request->validated();

        $projectDocumentations = ProjectDocumentation::query()
            ->with('author')
            ->when(!$request->user()->hasRole('admin'), fn (Builder $query) => $query->where('visibility', '!=', ProjectDocumentationVisibility::AdministratorsOnly))
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
                'type'   => $filters['type'] ?? '0',
            ],
            'categories'   => ProjectDocumentationCategory::options(),
            'types'        => ProjectDocumentationType::options(),
            'visibilities' => collect(ProjectDocumentationVisibility::options())
                ->when(
                    !$request->user()->hasRole('admin'),
                    fn ($options) => $options->reject(fn ($v) => $v['value'] === ProjectDocumentationVisibility::AdministratorsOnly->value),
                )
                ->values()
                ->all(),
        ]);
    }
}
