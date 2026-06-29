<?php

declare(strict_types = 1);

use App\Enums\Permission;
use App\Enums\Permissions\Projects\TaskPermissions;
use App\Models\ProjectRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from project role edition', function () {
    $projectRole = ProjectRole::factory()->create();

    $this->get(route('project-settings.roles.edit', $projectRole))->assertRedirect(route('login'));
});

test('users without permission cannot view project role edition', function () {
    $user        = User::factory()->create();
    $projectRole = ProjectRole::factory()->create();

    $this->actingAs($user)
        ->get(route('project-settings.roles.edit', $projectRole))
        ->assertForbidden();
});

test('project members with manage settings permission can view project role edition', function () {
    [$user, $project] = projectMemberWithSettingsPermission();
    $projectRole      = ProjectRole::factory()->for($project)->create();

    $this->actingAs($user)
        ->get(route('project-settings.roles.edit', $projectRole))
        ->assertOk();
});

test('admin users can view project role edition with role data and permission groups', function () {
    $user        = User::factory()->admin()->create();
    $projectRole = ProjectRole::factory()->create(['name' => 'project-manager']);

    $projectRole->syncPermissionNames([
        TaskPermissions::View->value,
        TaskPermissions::Update->value,
    ]);

    $this->actingAs($user)
        ->get(route('project-settings.roles.edit', $projectRole))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('project-settings/roles/Edit')
                ->where('projectRole.id', $projectRole->id)
                ->where('projectRole.name', 'project-manager')
                ->where('projectRole.permissions', [
                    TaskPermissions::View->value,
                    TaskPermissions::Update->value,
                ])
                ->where('permissionGroups', Permission::projectGroupedOptions()),
        );
});
