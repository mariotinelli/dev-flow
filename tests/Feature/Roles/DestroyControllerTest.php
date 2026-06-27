<?php

declare(strict_types = 1);

use App\Enums\Permissions\RolePermissions;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('guests are redirected when deleting roles', function () {
    $role = Role::create(['name' => 'guest-target']);

    $this->delete(route('roles.destroy', $role))->assertRedirect(route('login'));

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
    ]);
});

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
        ->delete(route('roles.destroy', $targetRole))
        ->assertForbidden();

    $this->assertDatabaseHas('roles', [
        'id' => $targetRole->id,
    ]);
});

test('users with delete role permission can delete roles', function () {
    $role = Role::create(['name' => 'role-destroyer']);
    $role->givePermissionTo(RolePermissions::Delete->value);

    $user       = User::factory()->withRole($role->name)->create();
    $targetRole = Role::create(['name' => 'permission-target']);

    $this->actingAs($user)
        ->delete(route('roles.destroy', $targetRole))
        ->assertRedirect(route('roles.index', absolute: false))
        ->assertToast('success', 'Perfil excluído.');

    $this->assertDatabaseMissing('roles', [
        'id' => $targetRole->id,
    ]);
});

test('admin users can delete editable roles', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'temporary']);

    $this->actingAs($user)
        ->delete(route('roles.destroy', $role))
        ->assertRedirect(route('roles.index', absolute: false))
        ->assertToast('success', 'Perfil excluído.');

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
