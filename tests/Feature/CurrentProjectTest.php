<?php

declare(strict_types = 1);

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Support\CurrentProject;
use Illuminate\Support\Facades\Cache;

beforeEach(fn () => Cache::store('array')->flush());

test('returns all non-deleted projects for admin users', function () {
    $admin = User::factory()->admin()->create();
    Project::factory()->create(['name' => 'Alpha']);
    Project::factory()->create(['name' => 'Bravo']);
    Project::factory()->create(['name' => 'Charlie']);

    $available = CurrentProject::availableFor($admin);

    expect($available)->toHaveCount(3)
        ->and($available->pluck('name')->toArray())->toEqual(['Alpha', 'Bravo', 'Charlie']);
});

test('returns only member projects for non-admin users', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->create();
    ProjectMember::factory()->create([
        'project_id' => $project->id,
        'user_id'    => $user->id,
    ]);
    Project::factory(2)->create();

    $available = CurrentProject::availableFor($user);

    expect($available)->toHaveCount(1)
        ->and($available->first()->id)->toBe($project->id);
});

test('excludes soft-deleted projects', function () {
    $admin = User::factory()->admin()->create();
    Project::factory(2)->create();
    $deleted = Project::factory()->create();
    $deleted->delete();

    $available = CurrentProject::availableFor($admin);

    expect($available)->toHaveCount(2);
});

test('resolves the selected project from session', function () {
    $admin    = User::factory()->admin()->create();
    $projectA = Project::factory()->create(['name' => 'Alpha']);
    Project::factory()->create(['name' => 'Bravo']);

    $this->actingAs($admin);

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn (Inertia\Testing\AssertableInertia $page) => $page
        ->where('auth.current_project.id', $projectA->id));
});

test('select stores the project id in session', function () {
    $admin   = User::factory()->admin()->create();
    $project = Project::factory()->create();

    $this->actingAs($admin);

    $this->post(route('projects.select', $project))
        ->assertSessionHas('selected_project_id', $project->id);
});

test('caches available projects per user', function () {
    $admin = User::factory()->admin()->create();
    Project::factory(2)->create();

    CurrentProject::availableFor($admin);

    $version = Cache::memo()->get('current_project:version');
    expect(Cache::memo()->has("current_project:available_for:{$admin->id}:v{$version}"))->toBeTrue();
});

test('cache version increments when a project is created', function () {
    $versionBefore = Cache::memo()->remember('current_project:version', 86400, fn () => 0);

    Project::factory()->create();

    $versionAfter = Cache::memo()->get('current_project:version');
    expect($versionAfter)->toBe($versionBefore + 1);
});

test('cache version increments when a project is updated', function () {
    $project = Project::factory()->create();
    $versionBefore = Cache::memo()->get('current_project:version');

    $project->update(['name' => 'Updated']);

    $versionAfter = Cache::memo()->get('current_project:version');
    expect($versionAfter)->toBe($versionBefore + 1);
});

test('cache version increments when a project is soft-deleted', function () {
    $project = Project::factory()->create();
    $versionBefore = Cache::memo()->get('current_project:version');

    $project->delete();

    $versionAfter = Cache::memo()->get('current_project:version');
    expect($versionAfter)->toBe($versionBefore + 1);
});

test('cache version increments when a project is restored', function () {
    $project = Project::factory()->create();
    $project->delete();

    $versionBefore = Cache::memo()->get('current_project:version');

    $project->restore();

    $versionAfter = Cache::memo()->get('current_project:version');
    expect($versionAfter)->toBe($versionBefore + 1);
});

test('cache version increments when a project member is created', function () {
    Project::factory()->create();

    $versionBefore = Cache::memo()->get('current_project:version');

    ProjectMember::factory()->create([
        'project_id' => 1,
        'user_id'    => User::factory()->create()->id,
    ]);

    $versionAfter = Cache::memo()->get('current_project:version');
    expect($versionAfter)->toBe($versionBefore + 1);
});

test('cache version increments when a project member is deleted', function () {
    $project = Project::factory()->create();
    $member  = ProjectMember::factory()->create([
        'project_id' => $project->id,
        'user_id'    => User::factory()->create()->id,
    ]);

    $versionBefore = Cache::memo()->get('current_project:version');

    $member->delete();

    $versionAfter = Cache::memo()->get('current_project:version');
    expect($versionAfter)->toBe($versionBefore + 1);
});

test('cache is isolated per user', function () {
    $admin1 = User::factory()->admin()->create();
    $admin2 = User::factory()->admin()->create();

    CurrentProject::availableFor($admin1);
    CurrentProject::availableFor($admin2);

    $version = Cache::memo()->get('current_project:version');
    expect($version)->not->toBeNull();

    expect(Cache::memo()->has("current_project:available_for:{$admin1->id}:v{$version}"))->toBeTrue()
        ->and(Cache::memo()->has("current_project:available_for:{$admin2->id}:v{$version}"))->toBeTrue();
});

test('returns fresh data after project creation', function () {
    $admin = User::factory()->admin()->create();

    $before = CurrentProject::availableFor($admin);
    expect($before)->toHaveCount(0);

    Project::factory()->create();

    $after = CurrentProject::availableFor($admin);
    expect($after)->toHaveCount(1);
});

test('returns fresh data after member removal', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->create();
    $member  = ProjectMember::factory()->create([
        'project_id' => $project->id,
        'user_id'    => $user->id,
    ]);

    $before = CurrentProject::availableFor($user);
    expect($before)->toHaveCount(1);

    $member->delete();

    $after = CurrentProject::availableFor($user);
    expect($after)->toHaveCount(0);
});
