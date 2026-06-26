<?php

declare(strict_types = 1);

use App\Enums\Permission;
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

test('admin users can view roles', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'used-role']);

    User::factory()->create()->assignRole($role);

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('roles/Index')
                ->where('roles.0.name', 'admin')
                ->where('roles.0.is_protected', true)
                ->where('roles.0.can.update', false)
                ->where('roles.0.can.delete', false)
                ->where('roles.1.name', 'used-role')
                ->where('roles.1.users_count', 1)
                ->where('roles.1.is_in_use', true)
                ->where('roles.1.can.delete', false)
                ->where('can.create', true),
        );
});
