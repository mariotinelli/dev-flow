<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\OverviewPermissions;
use App\Enums\Permissions\Projects\TaskPermissions;
use App\Enums\Permissions\UserPermissions;
use App\Models\Project;
use App\Models\ProjectRole;
use App\Models\User;

test('admin users can create project roles with selected project permissions', function () {
    $user    = User::factory()->admin()->create();
    $project = Project::factory()->create();

    $this->actingAs($user)
        ->post(route('project-settings.roles.store'), [
            'name'        => 'tech-lead',
            'permissions' => [
                TaskPermissions::View->value,
                TaskPermissions::Create->value,
            ],
        ])
        ->assertRedirect(route('project-settings.roles.index', absolute: false))
        ->assertToast('success', 'Papel do projeto cadastrado.');

    $projectRole = ProjectRole::query()->where('name', 'tech-lead')->firstOrFail();

    expect($projectRole->project_id)->toBe($project->id)
        ->and($projectRole->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([
            TaskPermissions::View->value,
            TaskPermissions::Create->value,
        ]);
});

test('project members with manage settings permission can create project roles', function () {
    [$user, $project] = projectMemberWithSettingsPermission();

    $this->actingAs($user)
        ->post(route('project-settings.roles.store'), [
            'name'        => 'project-viewer',
            'permissions' => [OverviewPermissions::View->value],
        ])
        ->assertRedirect(route('project-settings.roles.index', absolute: false));

    $this->assertDatabaseHas('project_roles', [
        'project_id' => $project->id,
        'name'       => 'project-viewer',
    ]);
});

test('guests are redirected when creating project roles', function () {
    $this->post(route('project-settings.roles.store'), [
        'name' => 'guest-project-role',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseMissing('project_roles', [
        'name' => 'guest-project-role',
    ]);
});

test('users without permission cannot create project roles', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('project-settings.roles.store'), [
            'name' => 'forbidden-project-role',
        ])
        ->assertForbidden();
});

test('project role name is required', function () {
    $user = User::factory()->admin()->create();
    Project::factory()->create();

    $this->actingAs($user)
        ->post(route('project-settings.roles.store'), [
            'permissions' => [TaskPermissions::View->value],
        ])
        ->assertSessionHasErrors(['name']);
});

test('project role name must be unique', function () {
    $user    = User::factory()->admin()->create();
    $project = Project::factory()->create();

    ProjectRole::factory()->for($project)->create(['name' => 'existing-project-role']);

    $this->actingAs($user)
        ->post(route('project-settings.roles.store'), [
            'name' => 'existing-project-role',
        ])
        ->assertSessionHasErrors(['name']);
});

test('permissions must be project scoped permissions', function () {
    $user = User::factory()->admin()->create();
    Project::factory()->create();

    $this->actingAs($user)
        ->post(route('project-settings.roles.store'), [
            'name'        => 'invalid-project-role-permissions',
            'permissions' => [UserPermissions::View->value],
        ])
        ->assertSessionHasErrors(['permissions.0']);
});
