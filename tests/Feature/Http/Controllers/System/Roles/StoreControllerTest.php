<?php

declare(strict_types = 1);

use App\Enums\Permissions\Project\TaskPermissions;
use App\Enums\Permissions\System\RolePermissions;
use App\Enums\Permissions\System\UserPermissions;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin users can create roles with selected permissions', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name'        => 'tech-manager',
            'permissions' => [
                UserPermissions::View->value,
                UserPermissions::Create->value,
            ],
        ])
        ->assertRedirect(route('system.roles.index', absolute: false))
        ->assertToast('success', 'Perfil cadastrado.');

    $role = Role::findByName('tech-manager');

    expect($role->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([
            UserPermissions::View->value,
            UserPermissions::Create->value,
        ]);
});

test('users with create role permission can create roles', function () {
    $role = Role::create(['name' => 'role-manager']);
    $role->givePermissionTo(RolePermissions::Create->value);

    $user = User::factory()->withRole($role->name)->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name'        => 'support-manager',
            'permissions' => [RolePermissions::View->value],
        ])
        ->assertRedirect(route('system.roles.index', absolute: false))
        ->assertToast('success', 'Perfil cadastrado.');

    $createdRole = Role::findByName('support-manager');

    expect($createdRole->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([RolePermissions::View->value]);
});

test('guests are redirected when creating roles', function () {
    $this->post(route('system.roles.store'), [
        'name' => 'guest-role',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseMissing('roles', [
        'name' => 'guest-role',
    ]);
});

test('users without permission cannot create roles', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name' => 'forbidden-role',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('roles', [
        'name' => 'forbidden-role',
    ]);
});

test('admin users can create roles without permissions', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name' => 'empty-permissions-role',
        ])
        ->assertRedirect(route('system.roles.index', absolute: false));

    $role = Role::findByName('empty-permissions-role');

    expect($role->guard_name)->toBe('web')
        ->and($role->permissions)->toBeEmpty();
});

test('admin users can create roles with role permissions', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name'        => 'profiles-supervisor',
            'permissions' => [
                RolePermissions::View->value,
                RolePermissions::Update->value,
            ],
        ])
        ->assertRedirect(route('system.roles.index', absolute: false));

    $role = Role::findByName('profiles-supervisor');

    expect($role->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([
            RolePermissions::View->value,
            RolePermissions::Update->value,
        ]);
});

test('role name is required', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'permissions' => [UserPermissions::View->value],
        ])
        ->assertSessionHasErrors(['name']);
});

test('role name must be a string', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name' => ['invalid-role'],
        ])
        ->assertSessionHasErrors(['name']);
});

test('admin role name cannot be created', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name' => 'admin',
        ])
        ->assertSessionHasErrors(['name']);
});

test('admin role name variations cannot be created', function (string $name) {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name' => $name,
        ])
        ->assertSessionHasErrors(['name']);

    $this->assertDatabaseMissing('roles', [
        'name' => $name,
    ]);
})->with([
    'uppercase' => 'Admin',
    'spaced'    => ' admin ',
]);

test('role name must be unique for web guard', function () {
    $user = User::factory()->admin()->create();

    Role::create(['name' => 'existing-role']);

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name' => 'existing-role',
        ])
        ->assertSessionHasErrors(['name']);
});

test('role name only needs to be unique for web guard', function () {
    $user = User::factory()->admin()->create();

    Role::create(['name' => 'shared-role', 'guard_name' => 'api']);

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name' => 'shared-role',
        ])
        ->assertRedirect(route('system.roles.index', absolute: false));

    $this->assertDatabaseHas('roles', [
        'name'       => 'shared-role',
        'guard_name' => 'web',
    ]);
});

test('role name cannot exceed 255 characters', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name' => str_repeat('a', 256),
        ])
        ->assertSessionHasErrors(['name']);
});

test('permissions must be an array', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name'        => 'invalid-permissions-role',
            'permissions' => UserPermissions::View->value,
        ])
        ->assertSessionHasErrors(['permissions']);

    $this->assertDatabaseMissing('roles', [
        'name' => 'invalid-permissions-role',
    ]);
});

test('permissions must be valid system permissions', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name'        => 'invalid-system-permissions-role',
            'permissions' => ['system.users.invalid'],
        ])
        ->assertSessionHasErrors(['permissions.0']);

    $this->assertDatabaseMissing('roles', [
        'name' => 'invalid-system-permissions-role',
    ]);
});

test('project scoped permissions cannot be assigned to system roles', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('system.roles.store'), [
            'name'        => 'project-scoped-system-role',
            'permissions' => [TaskPermissions::View->value],
        ])
        ->assertSessionHasErrors(['permissions.0']);

    $this->assertDatabaseMissing('roles', [
        'name' => 'project-scoped-system-role',
    ]);
});
