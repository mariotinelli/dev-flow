<?php

declare(strict_types = 1);

use App\Models\ProjectRole;
use App\Models\User;

test('guests are redirected when activating project roles', function () {
    $projectRole = ProjectRole::factory()->trashed()->create();

    $this->post(route('project-settings.roles.activate', $projectRole))->assertRedirect(route('login'));

    $this->assertSoftDeleted($projectRole);
});

test('users without permission cannot activate project roles', function () {
    $user        = User::factory()->create();
    $projectRole = ProjectRole::factory()->trashed()->create();

    $this->actingAs($user)
        ->post(route('project-settings.roles.activate', $projectRole))
        ->assertNotFound();
});

test('project members with manage settings permission can activate project roles', function () {
    [$user, $project] = projectMemberWithSettingsPermission();
    $projectRole      = ProjectRole::factory()->for($project)->trashed()->create();

    $this->actingAs($user)
        ->post(route('project-settings.roles.activate', $projectRole))
        ->assertRedirect(route('project-settings.roles.index', absolute: false))
        ->assertToast('success', 'Papel do projeto ativado.');

    expect($projectRole->refresh()->trashed())->toBeFalse();
});

test('admin users can activate project roles', function () {
    $user        = User::factory()->admin()->create();
    $projectRole = ProjectRole::factory()->trashed()->create();

    $this->actingAs($user)
        ->post(route('project-settings.roles.activate', $projectRole))
        ->assertRedirect(route('project-settings.roles.index', absolute: false));

    expect($projectRole->refresh()->trashed())->toBeFalse();
});
