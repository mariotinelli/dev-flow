<?php

declare(strict_types = 1);

namespace App\Support;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

final class CurrentProject
{
    private const SessionKey = 'selected_project_id';

    /**
     * @return Collection<int, Project>
     */
    public static function availableFor(User $user): Collection
    {
        $query = Project::query()
            ->whereNull('deleted_at')
            ->orderBy('name');

        if (!$user->hasRole('admin')) {
            $query->whereHas('members', fn ($query) => $query->where('user_id', $user->id));
        }

        return $query->get();
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
