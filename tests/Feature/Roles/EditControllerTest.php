<?php

declare(strict_types = 1);

use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin role cannot be edited', function () {
    $user      = User::factory()->admin()->create();
    $adminRole = Role::findByName('admin');

    $this->actingAs($user)
        ->get(route('roles.edit', $adminRole))
        ->assertForbidden();
});
