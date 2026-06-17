<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Enums\Permissions\DeveloperPermissions;
use App\Models\Developer;
use App\Models\User;
use App\Policies\Traits\CheckIsAdmin;

class DeveloperPolicy
{
    use CheckIsAdmin;
    
    public function viewAny(User $user): bool
    {
        return $user->can(DeveloperPermissions::View->value);
    }

    public function view(User $user, Developer $developer): bool
    {
        return $user->can(DeveloperPermissions::View->value);
    }

    public function create(User $user): bool
    {
        return $user->can(DeveloperPermissions::Create->value);
    }

    public function update(User $user, Developer $developer): bool
    {
        return $user->can(DeveloperPermissions::Update->value);
    }

    public function delete(User $user, Developer $developer): bool
    {
        return $user->can(DeveloperPermissions::Delete->value);
    }

    public function restore(User $user, Developer $developer): bool
    {
        return $user->can(DeveloperPermissions::Restore->value);
    }

    public function forceDelete(User $user, Developer $developer): bool
    {
        return false;
    }
}
