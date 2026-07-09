<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Enums\Permissions\Project\Settings\RolePermissions;
use App\Models\ProjectRole;
use App\Models\User;
use App\Policies\Traits\BelongsToCurrentProject;
use App\Policies\Traits\CheckIsAdmin;
use App\Support\CurrentProject;

class ProjectRolePolicy
{
    use BelongsToCurrentProject;
    use CheckIsAdmin;

    public function viewAny(User $user): bool
    {
        return app(CurrentProject::class)->hasPermission(RolePermissions::View);
    }

    public function view(User $user, ProjectRole $projectRole): bool
    {
        return app(CurrentProject::class)->hasPermission(RolePermissions::View) && $this->belongsToCurrentProject($projectRole);
    }

    public function create(User $user): bool
    {
        return app(CurrentProject::class)->hasPermission(RolePermissions::Create);
    }

    public function update(User $user, ProjectRole $projectRole): bool
    {
        return !$projectRole->trashed() && app(CurrentProject::class)->hasPermission(RolePermissions::Update) && $this->belongsToCurrentProject($projectRole);
    }

    public function delete(User $user, ProjectRole $projectRole): bool
    {
        return !$projectRole->trashed() && app(CurrentProject::class)->hasPermission(RolePermissions::Delete) && $this->belongsToCurrentProject($projectRole);
    }

    public function restore(User $user, ProjectRole $projectRole): bool
    {
        return $projectRole->trashed() && app(CurrentProject::class)->hasPermission(RolePermissions::Restore) && $this->belongsToCurrentProject($projectRole);
    }

    public function forceDelete(User $user, ProjectRole $projectRole): bool
    {
        return false;
    }
}
