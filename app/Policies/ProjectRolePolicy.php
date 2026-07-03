<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Enums\Permissions\Projects\SettingPermissions;
use App\Models\ProjectRole;
use App\Models\User;
use App\Policies\Traits\BelongsToCurrentProject;
use App\Support\CurrentProject;

class ProjectRolePolicy
{
    use BelongsToCurrentProject;

    public function viewAny(User $user): bool
    {
        return $this->canManageProjectSettings($user);
    }

    public function view(User $user, ProjectRole $projectRole): bool
    {
        return $this->canManageProjectSettings($user) && $this->belongsToCurrentProject($projectRole);
    }

    public function create(User $user): bool
    {
        return $this->canManageProjectSettings($user);
    }

    public function update(User $user, ProjectRole $projectRole): bool
    {
        return !$projectRole->trashed() && $this->canManageProjectSettings($user) && $this->belongsToCurrentProject($projectRole);
    }

    public function delete(User $user, ProjectRole $projectRole): bool
    {
        return !$projectRole->trashed() && $this->canManageProjectSettings($user) && $this->belongsToCurrentProject($projectRole);
    }

    public function restore(User $user, ProjectRole $projectRole): bool
    {
        return $projectRole->trashed() && $this->canManageProjectSettings($user) && $this->belongsToCurrentProject($projectRole);
    }

    public function forceDelete(User $user, ProjectRole $projectRole): bool
    {
        return false;
    }

    private function canManageProjectSettings(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return app(CurrentProject::class)->hasPermission(SettingPermissions::Manage->value);
    }
}
