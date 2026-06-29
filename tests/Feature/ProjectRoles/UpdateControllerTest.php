<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\MemberPermissions;
use App\Enums\Permissions\Projects\TaskPermissions;
use App\Enums\Permissions\UserPermissions;
use App\Models\Project;
use App\Models\ProjectRole;
use App\Models\User;

test('guests are redirected when updating project roles', function () {
    $projectRole = ProjectRole::factory()->create(['name' => 'guest-update-target']);

    $this->post(route('project-roles.update', $projectRole), [
        'name' => 'guest-updated-project-role',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseHas('project_roles', [
        'id'   => $projectRole->id,
        'name' => 'guest-update-target',
    ]);
});

test('users without permission cannot update project roles', function () {
    $user        = User::factory()->create();
    $projectRole = ProjectRole::factory()->create(['name' => 'forbidden-update-target']);

    $this->actingAs($user)
        ->post(route('project-roles.update', $projectRole), [
            'name' => 'forbidden-updated-project-role',
        ])
        ->assertForbidden();
});

test('project members with manage settings permission can update project roles', function () {
    [$user, $project] = projectMemberWithSettingsPermission();
    $projectRole      = ProjectRole::factory()->for($project)->create(['name' => 'permission-update-target']);

    $this->actingAs($user)
        ->post(route('project-roles.update', $projectRole), [
            'name'        => 'permission-updated-project-role',
            'permissions' => [MemberPermissions::View->value],
        ])
        ->assertRedirect(route('project-roles.index', absolute: false))
        ->assertToast('success', 'Papel do projeto atualizado.');

    $projectRole->refresh();

    expect($projectRole->name)->toBe('permission-updated-project-role')
        ->and($projectRole->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([MemberPermissions::View->value]);
});

test('admin users can update project roles without permissions', function () {
    $user        = User::factory()->admin()->create();
    $projectRole = ProjectRole::factory()->create(['name' => 'empty-update-target']);

    $projectRole->syncPermissionNames([TaskPermissions::View->value]);

    $this->actingAs($user)
        ->post(route('project-roles.update', $projectRole), [
            'name' => 'empty-updated-project-role',
        ])
        ->assertRedirect(route('project-roles.index', absolute: false));

    $projectRole->refresh();

    expect($projectRole->name)->toBe('empty-updated-project-role')
        ->and($projectRole->permissions)->toBeEmpty();
});

test('project role name must be unique when updating', function () {
    $user        = User::factory()->admin()->create();
    $project     = Project::factory()->create();
    $projectRole = ProjectRole::factory()->for($project)->create(['name' => 'unique-update-target']);

    ProjectRole::factory()->for($project)->create(['name' => 'existing-project-role']);

    $this->actingAs($user)
        ->post(route('project-roles.update', $projectRole), [
            'name' => 'existing-project-role',
        ])
        ->assertSessionHasErrors(['name']);
});

test('permissions must be project scoped permissions when updating', function () {
    $user        = User::factory()->admin()->create();
    $projectRole = ProjectRole::factory()->create();

    $this->actingAs($user)
        ->post(route('project-roles.update', $projectRole), [
            'name'        => 'invalid-update-permissions',
            'permissions' => [UserPermissions::View->value],
        ])
        ->assertSessionHasErrors(['permissions.0']);
});
