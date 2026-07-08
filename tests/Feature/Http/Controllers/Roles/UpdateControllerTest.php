<?php

declare(strict_types = 1);

use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\UserPermissions;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('guests are redirected when updating roles', function () {
    $role = Role::create(['name' => 'guest-update-target']);

    $this->post(route('roles.update', $role), [
        'name' => 'guest-updated-role',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseHas('roles', [
        'id'   => $role->id,
        'name' => 'guest-update-target',
    ]);
});

test('users without permission cannot update roles', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'forbidden-update-target']);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name' => 'forbidden-updated-role',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('roles', [
        'id'   => $role->id,
        'name' => 'forbidden-update-target',
    ]);
});

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

test('users with update role permission can update roles', function () {
    $role = Role::create(['name' => 'role-updater']);
    $role->givePermissionTo(RolePermissions::Update->value);

    $user       = User::factory()->withRole($role->name)->create();
    $targetRole = Role::create(['name' => 'permission-update-target']);

    $this->actingAs($user)
        ->post(route('roles.update', $targetRole), [
            'name'        => 'permission-updated-role',
            'permissions' => [RolePermissions::View->value],
        ])
        ->assertRedirect(route('roles.index', absolute: false))
        ->assertToast('success', 'Perfil atualizado.');

    $targetRole->refresh();

    expect($targetRole->name)->toBe('permission-updated-role')
        ->and($targetRole->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([RolePermissions::View->value]);
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
        ->assertRedirect(route('roles.index', absolute: false))
        ->assertToast('success', 'Perfil atualizado.');

    $role->refresh();

    expect($role->name)->toBe('developer-manager')
        ->and($role->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([UserPermissions::Update->value]);
});

test('admin users can update roles without permissions', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'empty-update-target']);

    $role->givePermissionTo(UserPermissions::View->value);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name' => 'empty-updated-role',
        ])
        ->assertRedirect(route('roles.index', absolute: false));

    $role->refresh();

    expect($role->name)->toBe('empty-updated-role')
        ->and($role->permissions)->toBeEmpty();
});

test('role name is required when updating', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'required-update-target']);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'permissions' => [UserPermissions::View->value],
        ])
        ->assertSessionHasErrors(['name']);
});

test('role name must be a string when updating', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'string-update-target']);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name' => ['invalid-role'],
        ])
        ->assertSessionHasErrors(['name']);
});

test('role name cannot be updated to admin', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'admin-name-update-target']);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name' => 'admin',
        ])
        ->assertSessionHasErrors(['name']);

    $this->assertDatabaseHas('roles', [
        'id'   => $role->id,
        'name' => 'admin-name-update-target',
    ]);
});

test('role name must be unique for web guard when updating', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'unique-update-target']);

    Role::create(['name' => 'existing-role']);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name' => 'existing-role',
        ])
        ->assertSessionHasErrors(['name']);
});

test('role name can keep its current value when updating', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'same-name-role']);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name' => 'same-name-role',
        ])
        ->assertRedirect(route('roles.index', absolute: false));

    $this->assertDatabaseHas('roles', [
        'id'   => $role->id,
        'name' => 'same-name-role',
    ]);
});

test('role name only needs to be unique for web guard when updating', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'api-guard-update-target']);

    Role::create(['name' => 'shared-update-role', 'guard_name' => 'api']);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name' => 'shared-update-role',
        ])
        ->assertRedirect(route('roles.index', absolute: false));

    $this->assertDatabaseHas('roles', [
        'id'         => $role->id,
        'name'       => 'shared-update-role',
        'guard_name' => 'web',
    ]);
});

test('role name cannot exceed 255 characters when updating', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'max-update-target']);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name' => str_repeat('a', 256),
        ])
        ->assertSessionHasErrors(['name']);
});

test('permissions must be an array when updating', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'array-permissions-update-target']);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name'        => 'invalid-permissions-update-target',
            'permissions' => UserPermissions::View->value,
        ])
        ->assertSessionHasErrors(['permissions']);
});

test('permissions must be valid system permissions when updating', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'valid-permissions-update-target']);

    $this->actingAs($user)
        ->post(route('roles.update', $role), [
            'name'        => 'invalid-system-permissions-update-target',
            'permissions' => ['users.invalid'],
        ])
        ->assertSessionHasErrors(['permissions.0']);
});
