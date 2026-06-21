<?php

declare(strict_types = 1);

use App\Enums\BaseStatus;
use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('guests are redirected from user management', function () {
    $this->get(route('users.index'))->assertRedirect(route('login'));
});

test('authenticated users without permission cannot view users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('users.index'))->assertForbidden();
});

test('authenticated users can view users', function () {
    $user = User::factory()->admin()->create();
    User::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('users.index'));

    $response->assertOk();
    $response->assertSee($user->name);
});

test('authenticated users can filter users', function () {
    $user = User::factory()->admin()->create();
    $role = Role::create(['name' => 'tech-lead']);

    $matchingUser = User::factory()->create([
        'name'          => 'Ada Lovelace',
        'email'         => 'ada@example.com',
        'job_title'     => JobTitle::AIEngineer,
        'contract_type' => ContractType::Fixed,
        'seniority'     => Seniority::Lead,
    ]);
    $matchingUser->assignRole($role);

    $otherUser = User::factory()->create([
        'job_title'     => JobTitle::QAEngineer,
        'contract_type' => ContractType::Freelance,
        'seniority'     => Seniority::Junior,
    ]);
    $otherUser->assignRole(Role::create(['name' => 'qa']));

    $this->actingAs($user)
        ->get(route('users.index', [
            'search'        => 'ada',
            'role'          => $role->id,
            'job_title'     => JobTitle::AIEngineer->value,
            'contract_type' => ContractType::Fixed->value,
            'seniority'     => Seniority::Lead->value,
            'status'        => BaseStatus::Active->value,
        ]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.id', $matchingUser->id)
                ->where('users.data.0.status_label', 'Ativo')
                ->where('filters.search', 'ada')
                ->where('filters.role', (string) $role->id)
                ->where('filters.job_title', (string) JobTitle::AIEngineer->value)
                ->where('filters.contract_type', (string) ContractType::Fixed->value)
                ->where('filters.seniority', (string) Seniority::Lead->value)
                ->where('filters.status', 'active'),
        );
});

test('user status is based on deleted at', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->trashed()->create();

    $this->actingAs($user)
        ->get(route('users.index', ['status' => BaseStatus::Inactive->value]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.id', $otherUser->id)
                ->where('users.data.0.is_active', false)
                ->where('users.data.0.status_label', 'Inativo')
                ->where('filters.status', 'inactive'),
        );
});

test('users list is paginated and keeps filters in pagination links', function () {
    $user = User::factory()->admin()->create();

    User::factory()
        ->count(13)
        ->create([
            'job_title'     => JobTitle::BackendDeveloper,
            'contract_type' => ContractType::Fixed,
            'seniority'     => Seniority::Senior,
        ]);

    $this->actingAs($user)
        ->get(route('users.index', [
            'job_title'     => JobTitle::BackendDeveloper->value,
            'contract_type' => ContractType::Fixed->value,
            'seniority'     => Seniority::Senior->value,
            'status'        => BaseStatus::Active->value,
            'page'          => 2,
        ]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('users/Index')
                ->has('users.data', 1)
                ->where('users.meta.current_page', 2)
                ->where('users.meta.last_page', 2)
                ->where('users.meta.total', 13)
                ->where('users.meta.links.0.url', fn (?string $url): bool => $url !== null && str_contains($url, 'contract_type=' . ContractType::Fixed->value)),
        );
});

test('authenticated users can create users and send a password setup link', function () {
    Notification::fake();

    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->post(route('users.store'), [
        'name'          => 'Ada Lovelace',
        'email'         => 'ada@example.com',
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Freelance->value,
        'seniority'     => Seniority::Senior->value,
    ]);

    $response
        ->assertRedirect(route('users.index', absolute: false))
        ->assertSessionHas('inertia.flash_data', [
            'toast' => [
                'type'    => 'success',
                'message' => 'Usuário cadastrado.',
            ],
        ]);

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

    $response = $this->actingAs($user)->post(route('users.store'), []);

    $response->assertSessionHasErrors(['name', 'email', 'job_title', 'contract_type', 'seniority']);
});

test('user email must be unique', function () {
    $user         = User::factory()->admin()->create();
    $existingUser = User::factory()->create();

    $response = $this->actingAs($user)->post(route('users.store'), [
        'name'          => 'Grace Hopper',
        'email'         => $existingUser->email,
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('authenticated users can update developers', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->create([
        'job_title'     => JobTitle::BackendDeveloper,
        'contract_type' => ContractType::Freelance,
        'seniority'     => Seniority::Junior,
    ]);

    $response = $this->actingAs($user)->post(route('users.update', $otherUser), [
        'name'          => 'Updated Name',
        'email'         => 'updated@example.com',
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
    ]);

    $response
        ->assertRedirect(route('users.index', absolute: false))
        ->assertSessionHas('inertia.flash_data', [
            'toast' => [
                'type'    => 'success',
                'message' => 'Usuário atualizado.',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id'            => $otherUser->id,
        'name'          => 'Updated Name',
        'email'         => 'updated@example.com',
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
    ]);
});

test('authenticated users can deactivate users', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('users.destroy', $otherUser));

    $response
        ->assertRedirect(route('users.index', absolute: false))
        ->assertSessionHas('inertia.flash_data', [
            'toast' => [
                'type'    => 'success',
                'message' => 'Usuário inativado.',
            ],
        ]);

    $this->assertSoftDeleted($otherUser);
});

test('authenticated users can activate users', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->trashed()->create();

    $response = $this->actingAs($user)->post(route('users.activate', $otherUser));

    $response
        ->assertRedirect(route('users.index', absolute: false))
        ->assertSessionHas('inertia.flash_data', [
            'toast' => [
                'type'    => 'success',
                'message' => 'Usuário ativado.',
            ],
        ]);

    $this->assertNotSoftDeleted($otherUser);
});

test('soft deleted user users cannot authenticate', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)->delete(route('users.destroy', $otherUser));
    auth()->logout();

    $this->post(route('login.store'), [
        'email'    => $otherUser->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});
