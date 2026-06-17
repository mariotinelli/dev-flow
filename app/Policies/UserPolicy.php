<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Enums\Permissions\UserPermissions;
use App\Models\User;
use App\Policies\Traits\CheckIsAdmin;

class UserPolicy
{
    use CheckIsAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can(UserPermissions::View->value);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(UserPermissions::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(UserPermissions::Create->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(UserPermissions::Update->value);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can(UserPermissions::Delete->value);
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can(UserPermissions::Restore->value);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
