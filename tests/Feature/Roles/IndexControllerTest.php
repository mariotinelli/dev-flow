<?php

declare(strict_types = 1);

use App\Enums\Permission;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\UserPermissions;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('admin role is created with all system permissions', function () {
    $admin = Role::findByName('admin');

    expect($admin->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing(Permission::values());
});

test('guests are redirected from role management', function () {
    $this->get(route('roles.index'))->assertRedirect(route('login'));
});

test('users without permission cannot view roles', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertForbidden();
});

test('users with view role permission can view roles', function () {
    $role = Role::create(['name' => 'role-viewer']);
    $role->givePermissionTo(RolePermissions::View->value);

    $user = User::factory()->withRole($role->name)->create();

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('roles/Index')
                ->where('can.create', false),
        );
});

test('admin users can view roles ordered with counts and permissions', function () {
    $user          = User::factory()->admin()->create();
    $availableRole = Role::create(['name' => 'available-role']);
    $usedRole      = Role::create(['name' => 'used-role']);

    $availableRole->givePermissionTo([
        RolePermissions::View->value,
        UserPermissions::View->value,
    ]);

    $usedRole->givePermissionTo(UserPermissions::Update->value);

    User::factory()->create()->assignRole($usedRole);

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('roles/Index')
                ->where('roles.0.name', 'admin')
                ->where('roles.0.permissions_count', count(Permission::values()))
                ->where('roles.0.users_count', 1)
                ->where('roles.0.is_protected', true)
                ->where('roles.0.is_in_use', true)
                ->where('roles.0.can.update', false)
                ->where('roles.0.can.delete', false)
                ->where('roles.1.name', 'available-role')
                ->where('roles.1.permissions_count', 2)
                ->where('roles.1.users_count', 0)
                ->where('roles.1.is_protected', false)
                ->where('roles.1.is_in_use', false)
                ->where('roles.1.can.update', true)
                ->where('roles.1.can.delete', true)
                ->where('roles.2.name', 'used-role')
                ->where('roles.2.permissions_count', 1)
                ->where('roles.2.users_count', 1)
                ->where('roles.2.is_protected', false)
                ->where('roles.2.is_in_use', true)
                ->where('roles.2.can.update', true)
                ->where('roles.2.can.delete', false)
                ->where('can.create', true),
        );
});
