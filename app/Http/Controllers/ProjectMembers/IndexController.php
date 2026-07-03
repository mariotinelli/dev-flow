<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectMembers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectMembers\IndexProjectMemberRequest;
use App\Http\Resources\ProjectMemberResource;
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
        $project = $this->currentProject->resolve($request);

        abort_unless($project, 404);

        $projectMembers = ProjectMember::query()
            ->whereBelongsTo($project)
            ->with(['user', 'projectRole'])
            ->filters($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('project-members/Index', [
            'projectMembers' => ProjectMemberResource::collection($projectMembers),
            'can'            => [
                'create' => $request->user()->can('create', ProjectMember::class),
            ],
            'filters' => [
                'search' => $filters['search'] ?? '',
            ],
            'users'        => $this->getUsers($request, $project),
            'projectRoles' => $this->getProjectRoles($project),
        ]);
    }

    private function getUsers(IndexProjectMemberRequest $request, Project $project): array
    {
        return User::query()
            ->withTrashed()
            ->whereKeyNot($request->user()->id)
            ->whereDoesntHave('projectMembers', fn ($query) => $query->where('project_id', $project->id))
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'value' => $user->id,
                'label' => "{$user->name} ({$user->email})",
            ])
            ->values();
    }

    private function getProjectRoles(Project $project): array
    {
        return ProjectRole::query()
            ->whereBelongsTo($project)
            ->orderBy('name')
            ->get()
            ->map(fn (ProjectRole $projectRole): array => [
                'value' => $projectRole->id,
                'label' => $projectRole->name,
            ])
            ->values();
    }
}
