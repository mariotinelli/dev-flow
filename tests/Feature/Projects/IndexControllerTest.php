<?php

declare(strict_types = 1);

use App\Enums\BaseStatus;
use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from project management', function () {
    $this->get(route('projects.index'))->assertRedirect(route('login'));
});

test('authenticated users without permission cannot view projects', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('projects.index'))->assertForbidden();
});

test('authenticated users can view projects', function () {
    $user = User::factory()->admin()->create();
    Project::factory()->count(3)->create();

    $this->actingAs($user)
        ->get(route('projects.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Index')
            ->has('projects.data', 3));
});

test('authenticated users can filter projects', function () {
    $user = User::factory()->admin()->create();

    $matchingProject = Project::factory()->create([
        'name'       => 'Dev Flow Platform',
        'key'        => 'DFLOW',
        'status'     => ProjectStatus::Active,
        'visibility' => ProjectVisibility::Internal,
    ]);

    Project::factory()->create([
        'name'       => 'Website',
        'status'     => ProjectStatus::Paused,
        'visibility' => ProjectVisibility::Public,
    ]);

    $this->actingAs($user)
        ->get(route('projects.index', [
            'search'         => 'flow',
            'status'         => ProjectStatus::Active->value,
            'visibility'     => ProjectVisibility::Internal->value,
            'deleted_status' => BaseStatus::Active->value,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Index')
            ->has('projects.data', 1)
            ->where('projects.data.0.id', $matchingProject->id)
            ->where('filters.search', 'flow')
            ->where('filters.status', (string) ProjectStatus::Active->value)
            ->where('filters.visibility', (string) ProjectVisibility::Internal->value)
            ->where('filters.deleted_status', BaseStatus::Active->value));
});

test('project filters only accept valid values', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('projects.index', [
            'status'         => 999,
            'visibility'     => 999,
            'deleted_status' => 'archived',
        ]))
        ->assertSessionHasErrors([
            'status',
            'visibility',
            'deleted_status',
        ]);
});

test('projects list is paginated and keeps filters in pagination links', function () {
    $user = User::factory()->admin()->create();

    Project::factory()->count(13)->create([
        'status'     => ProjectStatus::Active,
        'visibility' => ProjectVisibility::Internal,
    ]);

    $this->actingAs($user)
        ->get(route('projects.index', [
            'status'     => ProjectStatus::Active->value,
            'visibility' => ProjectVisibility::Internal->value,
            'page'       => 2,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Index')
            ->has('projects.data', 1)
            ->where('projects.meta.current_page', 2)
            ->where('projects.meta.last_page', 2)
            ->where('projects.meta.total', 13)
            ->where('projects.meta.links.0.url', fn (?string $url): bool => $url !== null && str_contains($url, 'visibility=' . ProjectVisibility::Internal->value)));
});
