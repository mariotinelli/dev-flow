<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Enums\Permissions\RolePermissions;
use App\Models\User;
use App\Policies\Traits\CheckIsAdmin;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(RolePermissions::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(RolePermissions::Create->value);
    }

    public function update(User $user, Role $role): bool
    {
        return $role->name !== 'admin' && $user->can(RolePermissions::Update->value);
    }

    public function delete(User $user, Role $role): bool
    {
        return $role->name !== 'admin' && $user->can(RolePermissions::Delete->value);
    }
}
