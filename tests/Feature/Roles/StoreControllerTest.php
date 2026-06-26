<?php

declare(strict_types = 1);

use App\Enums\Permissions\UserPermissions;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin users can create roles with selected permissions', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('roles.store'), [
            'name'        => 'tech-manager',
            'permissions' => [
                UserPermissions::View->value,
                UserPermissions::Create->value,
            ],
        ])
        ->assertRedirect(route('roles.index', absolute: false))
        ->assertToast('success', 'Perfil cadastrado.');

    $role = Role::findByName('tech-manager');

    expect($role->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([
            UserPermissions::View->value,
            UserPermissions::Create->value,
        ]);
});
