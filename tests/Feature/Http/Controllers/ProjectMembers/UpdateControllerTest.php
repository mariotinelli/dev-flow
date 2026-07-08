<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\MemberPermissions;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;

test('guests are redirected when updating project members', function () {
    $projectMember = ProjectMember::factory()->create();

    $this->post(route('project.members.update', $projectMember), [
        'user_id'         => $projectMember->user_id,
        'project_role_id' => $projectMember->project_role_id,
    ])->assertRedirect(route('login'));
});

test('users without update permission cannot update project members', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::View);
    $projectMember    = ProjectMember::factory()->create(['project_id' => $project->id]);

    $this->actingAs($user)
        ->post(route('project.members.update', $projectMember), [
            'user_id'         => $projectMember->user_id,
            'project_role_id' => $projectMember->project_role_id,
        ])
        ->assertForbidden();
});

test('project members with update permission can update project members', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::Update);
    $targetUser       = User::factory()->create();
    $projectRole      = ProjectRole::factory()->for($project)->create();
    $projectMember    = ProjectMember::factory()->create(['project_id' => $project->id]);

    $this->actingAs($user)
        ->post(route('project.members.update', $projectMember), [
            'user_id'         => $targetUser->id,
            'project_role_id' => $projectRole->id,
        ])
        ->assertRedirect(route('project.members.index', absolute: false))
        ->assertToast('success', 'Membro do projeto atualizado.');

    $projectMember->refresh();

    expect($projectMember->user_id)->toBe($targetUser->id)
        ->and($projectMember->project_role_id)->toBe($projectRole->id);
});

test('users cannot be duplicated when updating project members', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::Update);
    $existingMember   = ProjectMember::factory()->create(['project_id' => $project->id]);
    $projectMember    = ProjectMember::factory()->create(['project_id' => $project->id]);

    $this->actingAs($user)
        ->post(route('project.members.update', $projectMember), [
            'user_id'         => $existingMember->user_id,
            'project_role_id' => $projectMember->project_role_id,
        ])
        ->assertSessionHasErrors(['user_id']);
});

test('project roles from other projects are rejected when updating', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::Update);
    $projectMember    = ProjectMember::factory()->create(['project_id' => $project->id]);
    $otherRole        = ProjectRole::factory()->create();

    $this->actingAs($user)
        ->post(route('project.members.update', $projectMember), [
            'user_id'         => $projectMember->user_id,
            'project_role_id' => $otherRole->id,
        ])
        ->assertSessionHasErrors(['project_role_id']);
});
