<?php

declare(strict_types = 1);

use App\Models\ProjectRole;
use App\Models\User;

test('guests are redirected when deleting project roles', function () {
    $projectRole = ProjectRole::factory()->create();

    $this->delete(route('project.settings.roles.destroy', $projectRole))->assertRedirect(route('login'));

    $this->assertDatabaseHas('project_roles', [
        'id' => $projectRole->id,
    ]);
});

test('users without permission cannot delete project roles', function () {
    $user        = User::factory()->create();
    $projectRole = ProjectRole::factory()->create();

    $this->actingAs($user)
        ->delete(route('project.settings.roles.destroy', $projectRole))
        ->assertNotFound();
});

test('project members with manage settings permission can inactivate project roles', function () {
    [$user, $project] = projectMemberWithSettingsPermission();
    $projectRole      = ProjectRole::factory()->for($project)->create();

    $this->actingAs($user)
        ->delete(route('project.settings.roles.destroy', $projectRole))
        ->assertRedirect(route('project.settings.roles.index', absolute: false))
        ->assertToast('success', 'Papel do projeto inativado.');

    $this->assertSoftDeleted($projectRole);
});

test('admin users can inactivate project roles', function () {
    $user        = User::factory()->admin()->create();
    $projectRole = ProjectRole::factory()->create();

    $this->actingAs($user)
        ->delete(route('project.settings.roles.destroy', $projectRole))
        ->assertRedirect(route('project.settings.roles.index', absolute: false));

    $this->assertSoftDeleted($projectRole);
});
