<?php

declare(strict_types = 1);

namespace App\Http\Middleware;

use App\Enums\Permission;
use App\Enums\Permissions\ProjectPermissions;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\UserPermissions;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Support\CurrentProject;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        private CurrentProject $currentProject,
    ) {
    }

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $currentProject = $request->user() ? $this->currentProject->resolve($request) : null;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user'                => $request->user(),
                'permissions'         => $request->user() ? $this->systemPermissions($request) : [],
                'project_permissions' => $request->user() ? $this->projectPermissions($request, $currentProject) : [],
                'projects'            => $request->user() ? $this->projects($request) : [],
                'current_project'     => $currentProject ? $this->projectOption($currentProject) : null,
            ],
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function systemPermissions(Request $request): array
    {
        return [
            ProjectPermissions::View->value => $request->user()->hasRole('admin') || $request->user()->can(ProjectPermissions::View->value),
            RolePermissions::View->value    => $request->user()->hasRole('admin') || $request->user()->can(RolePermissions::View->value),
            UserPermissions::View->value    => $request->user()->hasRole('admin') || $request->user()->can(UserPermissions::View->value),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function projectPermissions(Request $request, ?Project $project): array
    {
        if ($request->user()->hasRole('admin')) {
            return collect(Permission::projectCases())
                ->mapWithKeys(fn (mixed $permission): array => [$permission->value => true])
                ->all();
        }

        $permissionNames = $project ? ProjectMember::query()
            ->where('project_id', $project->id)
            ->where('user_id', $request->user()->id)
            ->with('projectRole.permissions')
            ->first()
            ?->projectRole
            ?->permissions
            ->pluck('name')
            ->all() ?? [] : [];

        return collect(Permission::projectCases())
            ->mapWithKeys(fn (mixed $permission): array => [
                $permission->value => in_array($permission->value, $permissionNames, true),
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, key: string, color: string|null}>
     */
    private function projects(Request $request): array
    {
        return $this->currentProject->availableFor($request->user())
            ->map(fn (Project $project): array => $this->projectOption($project))
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, name: string, key: string, color: string|null}
     */
    private function projectOption(Project $project): array
    {
        return [
            'id'    => $project->id,
            'name'  => $project->name,
            'key'   => $project->key,
            'color' => $project->color,
        ];
    }
}
