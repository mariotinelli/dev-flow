<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectSettings\ProjectRoles;

use App\Enums\BaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRoles\IndexProjectRoleRequest;
use App\Http\Resources\ProjectRoleResource;
use App\Models\ProjectRole;
use App\Models\Scopes\BelongsToCurrentProjectScope;
use App\Support\CurrentProject;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    public function __invoke(IndexProjectRoleRequest $request): Response
    {
        $filters = $request->validated();

        $projectRoles = ProjectRole::query()
            ->withTrashed()
            ->withCount('permissions')
            ->filters($filters)
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $project = $this->currentProject->resolve($request);

        return Inertia::render('project-settings/roles/Index', [
            'projectRoles' => ProjectRoleResource::collection($projectRoles),
            'can'          => [
                'create' => $request->user()->can('create', ProjectRole::class),
            ],
            'filters' => [
                'search'         => $filters['search'] ?? '',
                'deleted_status' => $filters['deleted_status'] ?? 'all',
            ],
            'deletedStatuses' => BaseStatus::options(),
            'sourceProjects'  => $this->currentProject->availableFor($request->user())
                ->loadCount(['projectRoles' => fn ($query) => $query->withoutGlobalScope(BelongsToCurrentProjectScope::class)])
                ->reject(fn ($sourceProject): bool => $sourceProject->id === $project->id)
                ->filter(fn ($sourceProject): bool => $sourceProject->project_roles_count > 0)
                ->map(fn ($sourceProject): array => [
                    'id'          => $sourceProject->id,
                    'name'        => $sourceProject->name,
                    'key'         => $sourceProject->key,
                    'roles_count' => $sourceProject->project_roles_count,
                ])
                ->values(),
        ]);
    }
}
