<?php

declare(strict_types = 1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('guests are redirected from user editing', function () {
    $user = User::factory()->create();

    $this->get(route('users.edit', $user))->assertRedirect(route('login'));
});

test('authenticated users without permission cannot view user editing', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)->get(route('users.edit', $otherUser))->assertForbidden();
});

test('authenticated users can view user editing without admin role option', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->create();

    Role::create(['name' => 'developer']);

    $this->actingAs($user)
        ->get(route('users.edit', $otherUser))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('users/Edit')
                ->where('user.id', $otherUser->id)
                ->has('jobTitles')
                ->has('contractTypes')
                ->has('seniorities')
                ->where('roles', fn (mixed $roles): bool => collect($roles)->pluck('label')->contains('Developer')
                    && !collect($roles)->pluck('label')->contains('Admin')),
        );
});
