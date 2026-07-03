<?php

declare(strict_types = 1);

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CurrentProject
{
    private const SessionKey = 'selected_project_id';
    private const Ttl        = 86400; // 60 * 60 * 24 -> 1 day

    /**
     * @return Collection<int, Project>
     */
    public function availableFor(User $user): Collection
    {
        $version = Cache::memo()->remember(
            'current_project:version',
            self::Ttl,
            fn () => 0
        );

        return Cache::memo()->remember(
            "current_project:available_for:{$user->id}:v{$version}",
            self::Ttl,
            fn () => $this->queryAvailableFor($user)
        );
    }

    /**
     * @return Collection<int, Project>
     */
    private function queryAvailableFor(User $user): Collection
    {
        $query = Project::query()
            ->whereNull('deleted_at')
            ->orderBy('name');

        if (!$user->hasRole('admin')) {
            $query->whereHas('members', fn ($query) => $query->where('user_id', $user->id));
        }

        return $query->get();
    }

    public function clearCache(): void
    {
        Cache::memo()->increment('current_project:version');
    }

    public function resolve(?Request $request = null): ?Project
    {
        $request ??= request();

        $user = $request->user();

        if (!$user instanceof User) {
            return null;
        }

        $projects = $this->availableFor($user);

        if ($projects->isEmpty()) {
            $request->session()->forget(self::SessionKey);

            return null;
        }

        $selectedProjectId = (int) $request->session()->get(self::SessionKey);
        $project           = $projects->firstWhere('id', $selectedProjectId) ?? $projects->first();

        $request->session()->put(self::SessionKey, $project->id);

        return $project;
    }

    public function select(Project $project, ?Request $request = null): void
    {
        $request ??= request();

        $request->session()->put(self::SessionKey, $project->id);
    }

    public function currentMember(?Request $request = null): ?ProjectMember
    {
        $request ??= request();

        $project = $this->resolve($request);

        if (!$project) {
            return null;
        }

        return once(
            fn () => ProjectMember::query()
                ->whereBelongsTo($project)
                ->whereBelongsTo($request->user())
                ->with('projectRole.permissions')
                ->first()
        );
    }

    public function hasPermission(string $permission, ?Request $request = null): bool
    {
        $request ??= request();

        return $this->currentMember($request)
            ?->projectRole?->permissions
            ->contains('name', $permission) ?? false;
    }
}
