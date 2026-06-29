<?php

declare(strict_types = 1);

use App\Enums\Permission;
use App\Enums\Permissions\RolePermissions;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('guests are redirected from role creation', function () {
    $this->get(route('roles.create'))->assertRedirect(route('login'));
});

test('users without permission cannot view role creation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertForbidden();
});

test('users with create role permission can view role creation', function () {
    $role = Role::create(['name' => 'role-creator']);
    $role->givePermissionTo(RolePermissions::Create->value);

    $user = User::factory()->withRole($role->name)->create();

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertOk();
});

test('admin users can view role creation with permission groups', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('roles/Create')
                ->where('permissionGroups', Permission::systemGroupedOptions()),
        );
});
