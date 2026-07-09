<?php

declare(strict_types = 1);

use App\Enums\Permissions\Project\MemberPermissions;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\Scopes\BelongsToCurrentProjectScope;
use App\Models\User;

test('guests are redirected when creating project members', function () {
    $this->post(route('project.members.store'), [
        'user_id'         => User::factory()->create()->id,
        'project_role_id' => ProjectRole::factory()->create()->id,
    ])->assertRedirect(route('login'));
});

test('users without create permission cannot create project members', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::View);
    $targetUser       = User::factory()->create();
    $projectRole      = ProjectRole::factory()->for($project)->create();

    $this->actingAs($user)
        ->post(route('project.members.store'), [
            'user_id'         => $targetUser->id,
            'project_role_id' => $projectRole->id,
        ])
        ->assertForbidden();
});

test('project members with create permission can create project members', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::Create);
    $targetUser       = User::factory()->create();
    $projectRole      = ProjectRole::factory()->for($project)->create();

    $this->actingAs($user)
        ->post(route('project.members.store'), [
            'user_id'         => $targetUser->id,
            'project_role_id' => $projectRole->id,
        ])
        ->assertRedirect(route('project.members.index', absolute: false))
        ->assertToast('success', 'Membro do projeto cadastrado.');

    $this->assertDatabaseHas('project_members', [
        'project_id'      => $project->id,
        'user_id'         => $targetUser->id,
        'project_role_id' => $projectRole->id,
    ]);
});

test('admin users can create project members without being project members', function () {
    $user        = User::factory()->admin()->create();
    $project     = Project::factory()->create();
    $targetUser  = User::factory()->create();
    $projectRole = ProjectRole::factory()->for($project)->create();

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->post(route('project.members.store'), [
            'user_id'         => $targetUser->id,
            'project_role_id' => $projectRole->id,
        ])
        ->assertRedirect(route('project.members.index', absolute: false));

    $this->assertDatabaseHas('project_members', [
        'project_id' => $project->id,
        'user_id'    => $targetUser->id,
    ]);
});

test('users cannot be duplicated in the same project', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::Create);
    $targetUser       = User::factory()->create();
    $projectRole      = ProjectRole::factory()->for($project)->create();

    ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $targetUser->id,
        'project_role_id' => $projectRole->id,
    ]);

    $this->actingAs($user)
        ->post(route('project.members.store'), [
            'user_id'         => $targetUser->id,
            'project_role_id' => $projectRole->id,
        ])
        ->assertSessionHasErrors(['user_id']);
});

test('the same user can be added to different projects', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::Create);
    $targetUser       = User::factory()->create();
    $otherProject     = Project::factory()->create();
    $projectRole      = ProjectRole::factory()->for($project)->create();

    ProjectMember::factory()->create([
        'project_id'      => $otherProject->id,
        'user_id'         => $targetUser->id,
        'project_role_id' => ProjectRole::factory()->for($otherProject)->create()->id,
    ]);

    $this->actingAs($user)
        ->post(route('project.members.store'), [
            'user_id'         => $targetUser->id,
            'project_role_id' => $projectRole->id,
        ])
        ->assertRedirect(route('project.members.index', absolute: false));

    expect(ProjectMember::query()->withoutGlobalScope(BelongsToCurrentProjectScope::class)->where('user_id', $targetUser->id)->count())->toBe(2);
});

test('project roles from other projects are rejected', function () {
    [$user]      = projectMemberWithMemberPermissions(MemberPermissions::Create);
    $targetUser  = User::factory()->create();
    $projectRole = ProjectRole::factory()->create();

    $this->actingAs($user)
        ->post(route('project.members.store'), [
            'user_id'         => $targetUser->id,
            'project_role_id' => $projectRole->id,
        ])
        ->assertSessionHasErrors(['project_role_id']);
});
