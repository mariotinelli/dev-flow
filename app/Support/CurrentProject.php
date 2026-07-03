<?php

declare(strict_types = 1);

namespace App\Support;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class CurrentProject
{
    private const SessionKey = 'selected_project_id';
    private const Ttl = 86400; // 60 * 60 * 24 -> 1 day

    /**
     * @return Collection<int, Project>
     */
    public static function availableFor(User $user): Collection
    {
        $version = Cache::memo()->remember(
            'current_project:version',
            self::Ttl,
            fn () => 0
        );

        return Cache::memo()->remember(
            "current_project:available_for:{$user->id}:v{$version}",
            self::Ttl,
            fn () => self::queryAvailableFor($user)
        );
    }

    /**
     * @return Collection<int, Project>
     */
    private static function queryAvailableFor(User $user): Collection
    {
        $query = Project::query()
            ->whereNull('deleted_at')
            ->orderBy('name');

        if (!$user->hasRole('admin')) {
            $query->whereHas('members', fn ($query) => $query->where('user_id', $user->id));
        }

        return $query->get();
    }

    public static function clearCache(): void
    {
        Cache::memo()->increment('current_project:version');
    }

    public static function resolve(Request $request): ?Project
    {
        $user = $request->user();

        if (!$user instanceof User) {
            return null;
        }

        $projects = self::availableFor($user);

        if ($projects->isEmpty()) {
            $request->session()->forget(self::SessionKey);

            return null;
        }

        $selectedProjectId = (int) $request->session()->get(self::SessionKey);
        $project           = $projects->firstWhere('id', $selectedProjectId) ?? $projects->first();

        $request->session()->put(self::SessionKey, $project->id);

        return $project;
    }

    public static function select(Request $request, Project $project): void
    {
        $request->session()->put(self::SessionKey, $project->id);
    }
}
