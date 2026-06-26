<?php

declare(strict_types = 1);

use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Models\User;
use App\Notifications\UserPasswordSetupNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

test('authenticated users can create users and send a password setup link', function () {
    Notification::fake();

    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'developer']);

    $this->actingAs($user)->post(route('users.store'), [
        'name'          => 'Ada Lovelace',
        'email'         => 'ada@example.com',
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Freelance->value,
        'seniority'     => Seniority::Senior->value,
        'role_id'       => $role->id,
    ])
        ->assertRedirectToRoute('users.index')
        ->assertToast('success', 'Usuário cadastrado.');

    $createdUser = User::where('email', 'ada@example.com')->firstOrFail();

    $this->assertDatabaseHas('users', [
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Freelance->value,
        'seniority'     => Seniority::Senior->value,
    ]);

    Notification::assertSentTo($createdUser, UserPasswordSetupNotification::class);

    expect($createdUser->hasRole($role))->toBeTrue();
});

test('user creation validates required fields', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('users.store'), [])
        ->assertSessionHasErrors([
            'name'          => 'O campo nome é obrigatório.',
            'email'         => 'O campo e-mail é obrigatório.',
            'job_title'     => 'O campo cargo é obrigatório.',
            'contract_type' => 'O campo tipo de contrato é obrigatório.',
            'seniority'     => 'O campo senioridade é obrigatório.',
            'role_id'       => 'O campo perfil é obrigatório.',
        ]);
});

test('user email must be unique', function () {
    $user         = User::factory()->admin()->create();
    $existingUser = User::factory()->create();
    $role         = Role::create(['name' => 'developer']);

    $this->actingAs($user)->post(route('users.store'), [
        'name'          => 'Grace Hopper',
        'email'         => $existingUser->email,
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
        'role_id'       => $role->id,
    ])
        ->assertSessionHasErrors(['email']);
});
