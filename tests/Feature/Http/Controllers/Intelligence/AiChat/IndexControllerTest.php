<?php

declare(strict_types = 1);

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin can access the project ai chat screen', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Project::factory()->create();

    $this->actingAs($admin)
        ->get(route('intelligence.ai-chat.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('intelligence/ai-chat/Index')
            ->has('messages'));
});

test('project member with ai chat permission can access the screen', function () {
    [$user] = projectMemberWithAiChatPermission();

    $this->actingAs($user)
        ->get(route('intelligence.ai-chat.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('intelligence/ai-chat/Index')
            ->where('messages', []));
});

test('project member without ai chat permission cannot access the screen', function () {
    $project = Project::factory()->create();
    $role    = ProjectRole::factory()->for($project)->create();
    $user    = User::factory()->create();

    ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $user->id,
        'project_role_id' => $role->id,
    ]);

    $this->actingAs($user)
        ->get(route('intelligence.ai-chat.index'))
        ->assertForbidden();
});

test('non members cannot access the project scoped screen', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('intelligence.ai-chat.index'))
        ->assertNotFound();
});
