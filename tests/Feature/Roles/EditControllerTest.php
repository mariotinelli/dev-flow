<?php

declare(strict_types = 1);

use App\Enums\Permission;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\UserPermissions;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('guests are redirected from role edition', function () {
    $role = Role::create(['name' => 'guest-edit-target']);

    $this->get(route('roles.edit', $role))->assertRedirect(route('login'));
});

test('users without permission cannot view role edition', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'forbidden-edit-target']);

    $this->actingAs($user)
        ->get(route('roles.edit', $role))
        ->assertForbidden();
});

test('admin role cannot be edited', function () {
    $user      = User::factory()->admin()->create();
    $adminRole = Role::findByName('admin');

    $this->actingAs($user)
        ->get(route('roles.edit', $adminRole))
        ->assertForbidden();
});

test('users with update role permission can view role edition', function () {
    $role = Role::create(['name' => 'role-editor']);
    $role->givePermissionTo(RolePermissions::Update->value);

    $user       = User::factory()->withRole($role->name)->create();
    $targetRole = Role::create(['name' => 'permission-edit-target']);

    $this->actingAs($user)
        ->get(route('roles.edit', $targetRole))
        ->assertOk();
});

test('admin users can view role edition with role data and permission groups', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'support-manager']);

    $role->givePermissionTo([
        UserPermissions::View->value,
        UserPermissions::Update->value,
    ]);

    $this->actingAs($user)
        ->get(route('roles.edit', $role))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('roles/Edit')
                ->where('role.id', $role->id)
                ->where('role.name', 'support-manager')
                ->where('role.permissions', [
                    UserPermissions::View->value,
                    UserPermissions::Update->value,
                ])
                ->where('permissionGroups', Permission::groupedOptions()),
        );
});
