<?php

declare(strict_types = 1);

use App\Enums\Permissions\System\ProjectPermissions;
use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('guests are redirected from project editing', function () {
    $project = Project::factory()->create();

    $this->get(route('system.projects.edit', $project))->assertRedirect(route('login'));
});

test('authenticated users without permission cannot view project editing', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($user)->get(route('system.projects.edit', $project))->assertForbidden();
});

test('users with update project permission can view project editing', function () {
    $role = Role::create(['name' => 'project-editor']);
    $role->givePermissionTo(ProjectPermissions::Update->value);

    $user    = User::factory()->withRole($role->name)->create();
    $project = Project::factory()->create([
        'name'        => 'Dev Flow Platform',
        'key'         => 'DFLOW',
        'description' => 'Internal platform.',
        'color'       => '#1F8A70',
        'starts_at'   => '2026-07-01',
        'due_at'      => '2026-12-31',
    ]);

    $this->actingAs($user)
        ->get(route('system.projects.edit', $project))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('system/projects/Edit')
            ->where('project.id', $project->id)
            ->where('project.name', 'Dev Flow Platform')
            ->where('project.key', 'DFLOW')
            ->where('project.description', 'Internal platform.')
            ->where('project.color', '#1F8A70')
            ->where('project.starts_at', '2026-07-01')
            ->where('project.due_at', '2026-12-31'));
});
