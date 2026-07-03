<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Enums\Permissions\Projects\DocumentPermissions;
use App\Models\ProjectDocumentation;
use App\Models\User;
use App\Policies\Traits\BelongsToCurrentProject;
use App\Policies\Traits\CheckIsAdmin;
use App\Support\CurrentProject;

class ProjectDocumentationPolicy
{
    use BelongsToCurrentProject;
    use CheckIsAdmin;

    public function viewAny(User $user): bool
    {
        return $this->hasProjectPermission(DocumentPermissions::View);
    }

    public function view(User $user, ProjectDocumentation $projectDocumentation): bool
    {
        return $this->belongsToCurrentProject($projectDocumentation)
            && $this->hasProjectPermission(DocumentPermissions::View);
    }

    public function create(User $user): bool
    {
        return $this->hasProjectPermission(DocumentPermissions::Create);
    }

    public function update(User $user, ProjectDocumentation $projectDocumentation): bool
    {
        return $this->belongsToCurrentProject($projectDocumentation)
            && $this->hasProjectPermission(DocumentPermissions::Update);
    }

    public function delete(User $user, ProjectDocumentation $projectDocumentation): bool
    {
        return $this->belongsToCurrentProject($projectDocumentation)
            && $this->hasProjectPermission(DocumentPermissions::Delete);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProjectDocumentation $projectDocumentation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProjectDocumentation $projectDocumentation): bool
    {
        return false;
    }

    private function hasProjectPermission(DocumentPermissions $permission): bool
    {
        return app(CurrentProject::class)->hasPermission($permission->value);
    }
}
