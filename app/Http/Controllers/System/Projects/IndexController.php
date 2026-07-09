<?php

declare(strict_types = 1);

namespace App\Http\Controllers\System\Projects;

use App\Enums\BaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\System\Projects\IndexProjectRequest;
use App\Http\Resources\System\Projects\ProjectResource;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(IndexProjectRequest $request): Response
    {
        $filters = $request->validated();

        $projects = Project::query()
            ->withTrashed()
            ->filters($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('system/projects/Index', [
            'projects' => ProjectResource::collection($projects),
            'can'      => [
                'create' => $request->user()->can('create', Project::class),
            ],
            'filters' => [
                'search'         => $filters['search'] ?? '',
                'deleted_status' => $filters['deleted_status'] ?? 'all',
            ],
            'deletedStatuses' => BaseStatus::options(),
        ]);
    }
}
