<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Enums\Permissions\Intelligence\AiChatPermissions;
use App\Enums\Permissions\System\ProjectPermissions;
use App\Models\Project;
use App\Models\User;
use App\Policies\Traits\CheckIsAdmin;
use App\Support\CurrentProject;

class ProjectPolicy
{
    use CheckIsAdmin;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(ProjectPermissions::View->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->can(ProjectPermissions::View->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(ProjectPermissions::Create->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->can(ProjectPermissions::Update->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->can(ProjectPermissions::Delete->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->can(ProjectPermissions::Restore->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }

    public function useAiChat(User $user): bool
    {
        return app(CurrentProject::class)->hasPermission(AiChatPermissions::Use->value);
    }
}
