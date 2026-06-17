<?php

declare(strict_types = 1);

use App\Enums\Permission;
use App\Enums\Permissions\UserPermissions;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

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

    $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
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

test('admin users can create roles with selected permissions', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->post(route('roles.store'), [
        'name'        => 'tech-manager',
        'permissions' => [
            UserPermissions::View->value,
            UserPermissions::Create->value,
        ],
    ]);

    $response
        ->assertRedirect(route('roles.index', absolute: false))
        ->assertSessionHas('inertia.flash_data', [
            'toast' => [
                'type'    => 'success',
                'message' => 'Perfil cadastrado.',
            ],
        ]);

    $role = Role::findByName('tech-manager');

    expect($role->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([
            UserPermissions::View->value,
            UserPermissions::Create->value,
        ]);
});

test('admin role cannot be edited or deleted', function () {
    $user      = User::factory()->admin()->create();
    $adminRole = Role::findByName('admin');

    $this->actingAs($user)->get(route('roles.edit', $adminRole))->assertForbidden();

    $this->actingAs($user)
        ->post(route('roles.update', $adminRole), [
            'name'        => 'super-admin',
            'permissions' => [],
        ])
        ->assertForbidden();

    $this->actingAs($user)->delete(route('roles.destroy', $adminRole))->assertForbidden();

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

test('admin users can delete editable roles', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'temporary']);

    $this->actingAs($user)->delete(route('roles.destroy', $role))
        ->assertRedirect(route('roles.index', absolute: false));

    $this->assertDatabaseMissing('roles', [
        'id' => $role->id,
    ]);
});

test('admin users cannot delete roles assigned to users', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'assigned-role']);

    User::factory()->create()->assignRole($role);

    $this->actingAs($user)->delete(route('roles.destroy', $role))
        ->assertRedirect(route('roles.index', absolute: false))
        ->assertSessionHas('inertia.flash_data', [
            'toast' => [
                'type'    => 'error',
                'message' => 'Este perfil está em uso e não pode ser excluído.',
            ],
        ]);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
    ]);
});
