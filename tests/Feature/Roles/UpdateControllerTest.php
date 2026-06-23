<?php

declare(strict_types = 1);

use App\Enums\Permissions\UserPermissions;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin role cannot be updated', function () {
    $user      = User::factory()->admin()->create();
    $adminRole = Role::findByName('admin');

    $this->actingAs($user)
        ->post(route('roles.update', $adminRole), [
            'name'        => 'super-admin',
            'permissions' => [],
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('roles', [
        'id'   => $adminRole->id,
        'name' => 'admin',
    ]);
});

test('admin users can update editable roles', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'viewer']);

    $role->givePermissionTo(UserPermissions::View->value);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name'        => 'developer-manager',
            'permissions' => [UserPermissions::Update->value],
        ])
        ->assertRedirect(route('roles.index', absolute: false));

    $role->refresh();

    expect($role->name)->toBe('developer-manager')
        ->and($role->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([UserPermissions::Update->value]);
});
