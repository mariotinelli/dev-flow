<?php

declare(strict_types = 1);

use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('authenticated users can create users and send a password setup link', function () {
    Notification::fake();

    $user = User::factory()->admin()->create();

    $this->actingAs($user)->post(route('users.store'), [
        'name'          => 'Ada Lovelace',
        'email'         => 'ada@example.com',
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Freelance->value,
        'seniority'     => Seniority::Senior->value,
    ])
        ->assertRedirectToRoute('users.index')
        ->assertToast('success', 'Usuário cadastrado.');

    $createdUser = User::where('email', 'ada@example.com')->firstOrFail();

    $this->assertDatabaseHas('users', [
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Freelance->value,
        'seniority'     => Seniority::Senior->value,
    ]);

    Notification::assertSentTo($createdUser, ResetPassword::class);
});

test('user creation validates required fields', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('users.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'job_title', 'contract_type', 'seniority']);
});

test('user email must be unique', function () {
    $user         = User::factory()->admin()->create();
    $existingUser = User::factory()->create();

    $this->actingAs($user)->post(route('users.store'), [
        'name'          => 'Grace Hopper',
        'email'         => $existingUser->email,
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
    ])
        ->assertSessionHasErrors(['email']);
});
