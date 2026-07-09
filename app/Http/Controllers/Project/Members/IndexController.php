<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Project\Members;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\Members\IndexProjectMemberRequest;
use App\Http\Resources\Project\Members\ProjectMemberResource;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;
use App\Support\CurrentProject;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    public function __invoke(IndexProjectMemberRequest $request): Response
    {
        $filters = $request->validated();

        $projectMembers = ProjectMember::query()
            ->with(['user', 'projectRole'])
            ->filters($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('project/members/Index', [
            'projectMembers' => ProjectMemberResource::collection($projectMembers),
            'can'            => [
                'create' => $request->user()->can('create', ProjectMember::class),
            ],
            'filters' => [
                'search' => $filters['search'] ?? '',
            ],
            'users'        => $this->getUsers($request, $this->currentProject->resolve($request)),
            'projectRoles' => $this->getProjectRoles(),
        ]);
    }

    private function getUsers(IndexProjectMemberRequest $request, ?Project $project): array
    {
        $projectId = $project?->id;

        if (!$projectId) {
            return [];
        }

        return User::query()
            ->withTrashed()
            ->whereKeyNot($request->user()->id)
            ->whereDoesntHave('projectMembers', fn ($query) => $query->where('project_id', $projectId))
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'value' => $user->id,
                'label' => "{$user->name} ({$user->email})",
            ])
            ->values()
            ->all();
    }

    private function getProjectRoles(): array
    {
        return ProjectRole::query()
            ->orderBy('name')
            ->get()
            ->map(fn (ProjectRole $projectRole): array => [
                'value' => $projectRole->id,
                'label' => $projectRole->name,
            ])
            ->values()
            ->all();
    }
}
