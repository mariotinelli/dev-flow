<?php

namespace App\Policies\Traits;

use App\Models\User;

trait CheckIsAdmin
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user?->hasRole('admin')) {
            return true;
        }

        return null;
    }
}
