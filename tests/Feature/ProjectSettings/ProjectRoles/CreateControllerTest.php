<?php

declare(strict_types = 1);

use App\Enums\Permission;
use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from project role creation', function () {
    $this->get(route('project-settings.roles.create'))->assertRedirect(route('login'));
});

test('users without permission cannot view project role creation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('project-settings.roles.create'))
        ->assertNotFound();
});

test('project members with manage settings permission can view project role creation', function () {
    [$user] = projectMemberWithSettingsPermission();

    $this->actingAs($user)
        ->get(route('project-settings.roles.create'))
        ->assertOk();
});

test('admin users can view project role creation with project permission groups', function () {
    $user = User::factory()->admin()->create();
    Project::factory()->create();

    $this->actingAs($user)
        ->get(route('project-settings.roles.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('project-settings/roles/Create')
                ->where('permissionGroups', Permission::projectGroupedOptions()),
        );
});
