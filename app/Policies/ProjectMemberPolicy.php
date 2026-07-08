<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Enums\Permissions\Projects\MemberPermissions;
use App\Models\ProjectMember;
use App\Models\User;
use App\Policies\Traits\BelongsToCurrentProject;
use App\Policies\Traits\CheckIsAdmin;
use App\Support\CurrentProject;

class ProjectMemberPolicy
{
    use BelongsToCurrentProject;
    use CheckIsAdmin;

    public function viewAny(User $user): bool
    {
        return $this->hasProjectPermission($user, MemberPermissions::View);
    }

    public function view(User $user, ProjectMember $projectMember): bool
    {
        return $this->belongsToCurrentProject($projectMember)
            && $this->hasProjectPermission($user, MemberPermissions::View);
    }

    public function create(User $user): bool
    {
        return $this->hasProjectPermission($user, MemberPermissions::Create);
    }

    public function update(User $user, ProjectMember $projectMember): bool
    {
        return $this->belongsToCurrentProject($projectMember)
            && $this->hasProjectPermission($user, MemberPermissions::Update);
    }

    public function delete(User $user, ProjectMember $projectMember): bool
    {
        return $this->belongsToCurrentProject($projectMember)
            && $this->hasProjectPermission($user, MemberPermissions::Delete);
    }

    private function hasProjectPermission(User $user, MemberPermissions $permission): bool
    {
        return app(CurrentProject::class)->hasPermission($permission->value);
    }
}
