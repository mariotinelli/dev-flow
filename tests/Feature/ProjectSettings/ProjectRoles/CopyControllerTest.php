<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\MemberPermissions;
use App\Enums\Permissions\Projects\TaskPermissions;
use App\Models\Project;
use App\Models\ProjectRole;
use App\Models\User;

test('guests are redirected when copying project roles', function () {
    $this->post(route('project-settings.roles.copy'), [
        'source_project_id' => Project::factory()->create()->id,
    ])->assertRedirect(route('login'));
});

test('admin users can copy project roles from another project', function () {
    $user          = User::factory()->admin()->create();
    $targetProject = Project::factory()->create(['name' => 'Target Project']);
    $sourceProject = Project::factory()->create(['name' => 'Source Project']);

    $sourceDeveloper = ProjectRole::factory()->for($sourceProject)->create(['name' => 'developer']);
    $sourceManager   = ProjectRole::factory()->for($sourceProject)->create(['name' => 'manager']);
    $targetDeveloper = ProjectRole::factory()->for($targetProject)->create(['name' => 'developer']);

    $sourceDeveloper->syncPermissionNames([
        TaskPermissions::View->value,
        TaskPermissions::Create->value,
    ]);
    $sourceManager->syncPermissionNames([MemberPermissions::View->value]);
    $targetDeveloper->syncPermissionNames([TaskPermissions::Delete->value]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $targetProject->id])
        ->post(route('project-settings.roles.copy'), [
            'source_project_id' => $sourceProject->id,
        ])
        ->assertRedirect(route('project-settings.roles.index', absolute: false))
        ->assertToast('success', 'Papéis copiados para o projeto atual.');

    $targetDeveloper->refresh();
    $copiedManager = ProjectRole::query()
        ->whereBelongsTo($targetProject)
        ->where('name', 'manager')
        ->firstOrFail();

    expect(ProjectRole::query()->whereBelongsTo($targetProject)->count())->toBe(2)
        ->and($targetDeveloper->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([
            TaskPermissions::View->value,
            TaskPermissions::Create->value,
        ])
        ->and($copiedManager->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([MemberPermissions::View->value]);
});

test('source project must have project roles to be copied', function () {
    $user          = User::factory()->admin()->create();
    $targetProject = Project::factory()->create(['name' => 'Target Project']);
    $sourceProject = Project::factory()->create(['name' => 'Source Project']);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $targetProject->id])
        ->post(route('project-settings.roles.copy'), [
            'source_project_id' => $sourceProject->id,
        ])
        ->assertSessionHasErrors(['source_project_id']);
});
