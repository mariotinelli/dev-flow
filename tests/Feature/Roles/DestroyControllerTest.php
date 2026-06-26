<?php

declare(strict_types = 1);

use App\Enums\Permissions\RolePermissions;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin role cannot be deleted', function () {
    $user      = User::factory()->admin()->create();
    $adminRole = Role::findByName('admin');

    $this->actingAs($user)
        ->delete(route('roles.destroy', $adminRole))
        ->assertForbidden();

    $this->assertDatabaseHas('roles', [
        'id'   => $adminRole->id,
        'name' => 'admin',
    ]);
});

test('users without permission cannot delete roles', function () {
    $role = Role::create(['name' => 'viewer']);
    $user = User::factory()->withRole($role->name)->create();

    $targetRole = Role::create(['name' => 'temporary']);

    $this->actingAs($user)
        ->delete(route('roles.destroy', $role))
        ->assertForbidden();

    $this->assertDatabaseHas('roles', [
        'id' => $targetRole->id,
    ]);
});

test('admin users can delete editable roles', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'temporary']);

    $this->actingAs($user)
        ->delete(route('roles.destroy', $role))
        ->assertRedirect(route('roles.index', absolute: false));

    $this->assertDatabaseMissing('roles', [
        'id' => $role->id,
    ]);
});

test('admin users cannot delete roles assigned to users', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'assigned-role']);

    User::factory()->create()->assignRole($role);

    $this->actingAs($user)
        ->delete(route('roles.destroy', $role))
        ->assertRedirect(route('roles.index', absolute: false))
        ->assertToast('error', 'Este perfil está em uso e não pode ser excluído.');

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
    ]);
});
