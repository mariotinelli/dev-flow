<?php

declare(strict_types = 1);

use App\Enums\Permission;
use App\Enums\Permissions\Project\OverviewPermissions;
use App\Enums\Permissions\Project\TaskPermissions;
use App\Enums\Permissions\System\RolePermissions;
use App\Models\Project;
use App\Models\ProjectRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('admin role receives all system and project scoped permissions', function () {
    $admin = Role::findByName('admin');

    expect($admin->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing(Permission::values());
});

test('guests are redirected from project role management', function () {
    $this->get(route('project.settings.roles.index'))->assertRedirect(route('login'));
});

test('users without permission cannot view project roles', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('project.settings.roles.index'))
        ->assertNotFound();
});

test('project members with manage settings permission can view project roles', function () {
    [$user] = projectMemberWithSettingsPermission();

    $this->actingAs($user)
        ->get(route('project.settings.roles.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('project/settings/roles/Index')
                ->where('can.create', true),
        );
});

test('admin users can view project roles ordered with status and permissions', function () {
    $user         = User::factory()->admin()->create();
    $project      = Project::factory()->create();
    $activeRole   = ProjectRole::factory()->for($project)->create(['name' => 'developer']);
    $inactiveRole = ProjectRole::factory()->for($project)->trashed()->create(['name' => 'viewer']);

    $activeRole->syncPermissionNames([
        TaskPermissions::View->value,
        TaskPermissions::Create->value,
    ]);

    $inactiveRole->syncPermissionNames([
        OverviewPermissions::View->value,
    ]);

    $this->actingAs($user)
        ->get(route('project.settings.roles.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('project/settings/roles/Index')
                ->where('projectRoles.data.0.name', 'developer')
                ->where('projectRoles.data.0.permissions_count', 2)
                ->where('projectRoles.data.0.is_active', true)
                ->where('projectRoles.data.0.can.update', true)
                ->where('projectRoles.data.0.can.delete', true)
                ->where('projectRoles.data.0.can.restore', false)
                ->where('projectRoles.data.1.name', 'viewer')
                ->where('projectRoles.data.1.permissions_count', 1)
                ->where('projectRoles.data.1.is_active', false)
                ->where('projectRoles.data.1.can.update', false)
                ->where('projectRoles.data.1.can.delete', false)
                ->where('projectRoles.data.1.can.restore', true)
                ->where('can.create', true),
        );
});

test('system role permissions do not appear in project role permission groups', function () {
    $user = User::factory()->admin()->create();
    Project::factory()->create();

    $this->actingAs($user)
        ->get(route('project.settings.roles.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('project/settings/roles/Create')
                ->where('permissionGroups', Permission::projectGroupedOptions())
                ->missing('permissionGroups.Papéis do Projeto')
                ->missing('permissionGroups.Perfis e permissões')
                ->missing('permissionGroups.Projetos'),
        );
});

test('project scoped permissions do not appear in system role permission groups', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('system.roles.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('system/roles/Create')
                ->where('permissionGroups', Permission::systemGroupedOptions())
                ->missing('permissionGroups.Tarefas')
                ->missing('permissionGroups.Configurações - Papéis')
                ->missing('permissionGroups.Configurações - Gitlab')
                ->missing('permissionGroups.Configurações - Loom')
                ->where('permissionGroups.Perfis e permissões.0.name', RolePermissions::View->value),
        );
});
