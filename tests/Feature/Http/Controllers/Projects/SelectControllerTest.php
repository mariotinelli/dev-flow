<?php

declare(strict_types = 1);

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('regular users see only projects where they are members', function () {
    $user          = User::factory()->create();
    $memberProject = Project::factory()->create(['name' => 'Member Project']);
    $otherProject  = Project::factory()->create(['name' => 'Other Project']);
    $role          = ProjectRole::factory()->for($memberProject)->create();

    ProjectMember::factory()->create([
        'project_id'      => $memberProject->id,
        'user_id'         => $user->id,
        'project_role_id' => $role->id,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where('auth.projects.0.id', $memberProject->id)
                ->where('auth.projects.0.name', 'Member Project')
                ->missing('auth.projects.1')
                ->where('auth.current_project.id', $memberProject->id),
        )
        ->assertSessionHas('selected_project_id', $memberProject->id);

    expect($otherProject->exists)->toBeTrue();
});

test('admin users see all active projects', function () {
    $user          = User::factory()->admin()->create();
    $firstProject  = Project::factory()->create(['name' => 'Alpha Project']);
    $secondProject = Project::factory()->create(['name' => 'Beta Project']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where('auth.projects.0.id', $firstProject->id)
                ->where('auth.projects.1.id', $secondProject->id)
                ->where('auth.current_project.id', $firstProject->id),
        )
        ->assertSessionHas('selected_project_id', $firstProject->id);
});

test('users can select one of their projects', function () {
    $user          = User::factory()->create();
    $firstProject  = Project::factory()->create(['name' => 'Alpha Project']);
    $secondProject = Project::factory()->create(['name' => 'Beta Project']);

    foreach ([$firstProject, $secondProject] as $project) {
        $role = ProjectRole::factory()->for($project)->create();

        ProjectMember::factory()->create([
            'project_id'      => $project->id,
            'user_id'         => $user->id,
            'project_role_id' => $role->id,
        ]);
    }

    $this->actingAs($user)
        ->from(route('dashboard'))
        ->post(route('projects.select', $secondProject))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.current_project.id', $secondProject->id));
});

test('regular users cannot select projects where they are not members', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($user)
        ->post(route('projects.select', $project))
        ->assertForbidden();
});
