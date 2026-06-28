<?php

declare(strict_types = 1);

use App\Enums\Permissions\ProjectPermissions;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('guests are redirected from project creation', function () {
    $this->get(route('projects.create'))->assertRedirect(route('login'));
});

test('authenticated users without permission cannot view project creation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('projects.create'))->assertForbidden();
});

test('users with create project permission can view project creation', function () {
    $role = Role::create(['name' => 'project-creator']);
    $role->givePermissionTo(ProjectPermissions::Create->value);

    $user = User::factory()->withRole($role->name)->create();

    $this->actingAs($user)
        ->get(route('projects.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('projects/Create'));
});
