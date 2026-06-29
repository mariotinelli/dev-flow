<?php

declare(strict_types = 1);

use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from project settings', function () {
    $this->get(route('project-roles.index'))->assertRedirect(route('login'));
});

test('admin users can view project role settings', function () {
    $user = User::factory()->admin()->create();
    Project::factory()->create();

    $this->actingAs($user)
        ->get(route('project-roles.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('project-settings/Roles'));
});

test('admin users can view gitlab settings', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('project-settings.gitlab'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('project-settings/Gitlab'));
});

test('admin users can view loom settings', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('project-settings.loom'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('project-settings/Loom'));
});
