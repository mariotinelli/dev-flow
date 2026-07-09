<?php

declare(strict_types = 1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('guests are redirected from user creation', function () {
    $this->get(route('system.users.create'))->assertRedirect(route('login'));
});

test('authenticated users without permission cannot view user creation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('system.users.create'))->assertForbidden();
});

test('authenticated users can view user creation without admin role option', function () {
    $user = User::factory()->admin()->create();

    Role::create(['name' => 'developer']);

    $this->actingAs($user)
        ->get(route('system.users.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('system/users/Create')
                ->has('jobTitles')
                ->has('contractTypes')
                ->has('seniorities')
                ->where('roles', fn (mixed $roles): bool => collect($roles)->pluck('label')->contains('Developer')
                    && !collect($roles)->pluck('label')->contains('Admin')),
        );
});
