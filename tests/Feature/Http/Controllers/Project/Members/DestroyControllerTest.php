<?php

declare(strict_types = 1);

use App\Enums\Permissions\Project\MemberPermissions;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;

test('guests are redirected when deleting project members', function () {
    $projectMember = ProjectMember::factory()->create();

    $this->delete(route('project.members.destroy', $projectMember))->assertRedirect(route('login'));

    $this->assertDatabaseHas('project_members', ['id' => $projectMember->id]);
});

test('users without delete permission cannot delete project members', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::View);
    $projectMember    = ProjectMember::factory()->create(['project_id' => $project->id]);

    $this->actingAs($user)
        ->delete(route('project.members.destroy', $projectMember))
        ->assertForbidden();
});

test('project members with delete permission can remove project members without deleting users', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::Delete);
    $memberUser       = User::factory()->create();
    $projectRole      = ProjectRole::factory()->for($project)->create();
    $projectMember    = ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $memberUser->id,
        'project_role_id' => $projectRole->id,
    ]);

    $this->actingAs($user)
        ->delete(route('project.members.destroy', $projectMember))
        ->assertRedirect(route('project.members.index', absolute: false))
        ->assertToast('success', 'Membro removido do projeto.');

    $this->assertDatabaseMissing('project_members', ['id' => $projectMember->id]);
    $this->assertDatabaseHas('users', ['id' => $memberUser->id]);

    ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $memberUser->id,
        'project_role_id' => $projectRole->id,
    ]);

    $this->assertDatabaseHas('project_members', [
        'project_id' => $project->id,
        'user_id'    => $memberUser->id,
    ]);
});

test('admin users can remove project members without being project members', function () {
    $user          = User::factory()->admin()->create();
    $project       = Project::factory()->create();
    $projectMember = ProjectMember::factory()->create(['project_id' => $project->id]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->delete(route('project.members.destroy', $projectMember))
        ->assertRedirect(route('project.members.index', absolute: false));

    $this->assertDatabaseMissing('project_members', ['id' => $projectMember->id]);
});
