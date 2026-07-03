<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\MemberPermissions;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from project member management', function () {
    $this->get(route('project.members.index'))->assertRedirect(route('login'));
});

test('users without permission cannot view project members', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('project.members.index'))
        ->assertForbidden();
});

test('project members with view permission can view project members', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::View);

    $this->actingAs($user)
        ->get(route('project.members.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('project-members/Index')
                ->where('projectMembers.data.0.user.id', $user->id)
                ->where('projectMembers.data.0.project_role.name', $project->projectRoles()->first()->name)
                ->missing('projectMembers.data.0.user.is_active')
                ->missing('projectMembers.data.0.user.status_label')
                ->missing('projectMembers.data.0.project_role.is_active')
                ->missing('filters.status')
                ->missing('statuses')
                ->where('projectMembers.data.0.can.update', false)
                ->where('projectMembers.data.0.can.delete', false)
                ->where('can.create', false),
        );
});

test('admin users can view members from the selected project without being a member', function () {
    $user         = User::factory()->admin()->create();
    $project      = Project::factory()->create(['name' => 'Selected Project']);
    $otherProject = Project::factory()->create(['name' => 'Other Project']);
    $projectRole  = ProjectRole::factory()->for($project)->create(['name' => 'Developer']);
    $otherRole    = ProjectRole::factory()->for($otherProject)->create(['name' => 'Viewer']);
    $memberUser   = User::factory()->create(['name' => 'Maria Silva', 'email' => 'maria@example.com']);

    ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $memberUser->id,
        'project_role_id' => $projectRole->id,
    ]);

    ProjectMember::factory()->create([
        'project_id'      => $otherProject->id,
        'user_id'         => User::factory()->create()->id,
        'project_role_id' => $otherRole->id,
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.members.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('project-members/Index')
                ->has('projectMembers.data', 1)
                ->where('projectMembers.data.0.user.name', 'Maria Silva')
                ->where('projectMembers.data.0.user.email', 'maria@example.com')
                ->where('projectMembers.data.0.project_role.name', 'Developer')
                ->where('projectMembers.data.0.can.update', true)
                ->where('projectMembers.data.0.can.delete', true)
                ->where('can.create', true),
        );
});

test('index provides modal options with users outside the selected project', function () {
    [$user, $project] = projectMemberWithMemberPermissions(MemberPermissions::View, MemberPermissions::Create);
    $availableUser    = User::factory()->create(['name' => 'Available User']);
    $projectRole      = ProjectRole::factory()->for($project)->create(['name' => 'Contributor']);

    $this->actingAs($user)
        ->get(route('project.members.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('project-members/Index')
                ->where('users.0.value', $availableUser->id)
                ->where('projectRoles.0.value', $projectRole->id)
                ->missing('users.1'),
        );
});
