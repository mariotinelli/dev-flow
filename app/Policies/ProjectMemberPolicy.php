<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Enums\Permissions\Projects\MemberPermissions;
use App\Models\ProjectMember;
use App\Models\User;
use App\Policies\Traits\CheckIsAdmin;
use App\Support\CurrentProject;

class ProjectMemberPolicy
{
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

    public function restore(User $user, ProjectMember $projectMember): bool
    {
        return false;
    }

    public function forceDelete(User $user, ProjectMember $projectMember): bool
    {
        return false;
    }

    private function hasProjectPermission(User $user, MemberPermissions $permission): bool
    {
        $project = CurrentProject::resolve(request());

        if (!$project) {
            return false;
        }

        return ProjectMember::query()
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->whereHas('projectRole.permissions', fn ($query) => $query->where('name', $permission->value))
            ->exists();
    }

    private function belongsToCurrentProject(ProjectMember $projectMember): bool
    {
        return $projectMember->project_id === CurrentProject::resolve(request())?->id;
    }
}
