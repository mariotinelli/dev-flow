<?php

declare(strict_types = 1);

use App\Enums\ContractType;
use App\Enums\Seniority;
use App\Models\Developer;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from developer management', function () {
    $this->get(route('developers.index'))->assertRedirect(route('login'));
});

test('authenticated users can view developers', function () {
    $user      = User::factory()->create();
    $developer = Developer::factory()->create();

    $response = $this->actingAs($user)->get(route('developers.index'));

    $response->assertOk();
    $response->assertSee($developer->user->name);
});

test('authenticated users can filter developers', function () {
    $user = User::factory()->create();

    $matchingUser = User::factory()->create([
        'name'  => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]);

    $matchingDeveloper = Developer::factory()->create([
        'user_id'       => $matchingUser->id,
        'role'          => 'Backend Developer',
        'contract_type' => ContractType::Fixed,
        'seniority'     => Seniority::Lead,
    ]);

    Developer::factory()->create([
        'role'          => 'Frontend Developer',
        'contract_type' => ContractType::Freelance,
        'seniority'     => Seniority::Junior,
    ]);

    $this->actingAs($user)
        ->get(route('developers.index', [
            'search'        => 'ada',
            'contract_type' => ContractType::Fixed->value,
            'seniority'     => Seniority::Lead->value,
            'status'        => 'active',
        ]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('developers/Index')
                ->has('developers.data', 1)
                ->where('developers.data.0.id', $matchingDeveloper->id)
                ->where('developers.data.0.status_label', 'Ativo')
                ->where('filters.search', 'ada')
                ->where('filters.contract_type', (string) ContractType::Fixed->value)
                ->where('filters.seniority', (string) Seniority::Lead->value)
                ->where('filters.status', 'active'),
        );
});

test('developer status is based on deleted at', function () {
    $user      = User::factory()->create();
    $developer = Developer::factory()->create();

    $developer->user->delete();
    $developer->delete();

    $this->actingAs($user)
        ->get(route('developers.index', ['status' => 'inactive']))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('developers/Index')
                ->has('developers.data', 1)
                ->where('developers.data.0.id', $developer->id)
                ->where('developers.data.0.is_active', false)
                ->where('developers.data.0.status_label', 'Inativo')
                ->where('filters.status', 'inactive'),
        );
});

test('developers list is paginated and keeps filters in pagination links', function () {
    $user = User::factory()->create();

    Developer::factory()
        ->count(13)
        ->create([
            'contract_type' => ContractType::Fixed,
            'seniority'     => Seniority::Senior,
        ]);

    $this->actingAs($user)
        ->get(route('developers.index', [
            'contract_type' => ContractType::Fixed->value,
            'seniority'     => Seniority::Senior->value,
            'status'        => 'active',
            'page'          => 2,
        ]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('developers/Index')
                ->has('developers.data', 1)
                ->where('developers.meta.current_page', 2)
                ->where('developers.meta.last_page', 2)
                ->where('developers.meta.total', 13)
                ->where('developers.meta.links.0.url', fn (?string $url): bool => $url !== null && str_contains($url, 'contract_type=' . ContractType::Fixed->value)),
        );
});

test('authenticated users can create developers and send a password setup link', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('developers.store'), [
        'name'          => 'Ada Lovelace',
        'email'         => 'ada@example.com',
        'role'          => 'Backend Developer',
        'contract_type' => ContractType::Freelance->value,
        'seniority'     => Seniority::Senior->value,
    ]);

    $response
        ->assertRedirect(route('developers.index', absolute: false))
        ->assertSessionHas('inertia.flash_data', [
            'toast' => [
                'type'    => 'success',
                'message' => 'Desenvolvedor criado e link para definir senha enviado.',
            ],
        ]);

    $developerUser = User::where('email', 'ada@example.com')->firstOrFail();

    $this->assertDatabaseHas('developers', [
        'user_id'       => $developerUser->id,
        'role'          => 'Backend Developer',
        'contract_type' => ContractType::Freelance->value,
        'seniority'     => Seniority::Senior->value,
    ]);

    Notification::assertSentTo($developerUser, ResetPassword::class);
});

test('developer creation validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('developers.store'), []);

    $response->assertSessionHasErrors(['name', 'email', 'role', 'contract_type', 'seniority']);
});

test('developer email must be unique', function () {
    $user              = User::factory()->create();
    $existingDeveloper = Developer::factory()->create();

    $response = $this->actingAs($user)->post(route('developers.store'), [
        'name'          => 'Grace Hopper',
        'email'         => $existingDeveloper->user->email,
        'role'          => 'Frontend Developer',
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('authenticated users can update developers', function () {
    $user      = User::factory()->create();
    $developer = Developer::factory()->create([
        'contract_type' => ContractType::Freelance,
        'seniority'     => Seniority::Junior,
    ]);

    $response = $this->actingAs($user)->post(route('developers.update', $developer), [
        'name'          => 'Updated Name',
        'email'         => 'updated@example.com',
        'role'          => 'Tech Lead',
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
    ]);

    $response
        ->assertRedirect(route('developers.index', absolute: false))
        ->assertSessionHas('inertia.flash_data', [
            'toast' => [
                'type'    => 'success',
                'message' => 'Desenvolvedor atualizado.',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id'    => $developer->user_id,
        'name'  => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $this->assertDatabaseHas('developers', [
        'id'            => $developer->id,
        'role'          => 'Tech Lead',
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
    ]);
});

test('authenticated users can deactivate developers and their users', function () {
    $user          = User::factory()->create();
    $developer     = Developer::factory()->create();
    $developerUser = $developer->user;

    $response = $this->actingAs($user)->delete(route('developers.destroy', $developer));

    $response
        ->assertRedirect(route('developers.index', absolute: false))
        ->assertSessionHas('inertia.flash_data', [
            'toast' => [
                'type'    => 'success',
                'message' => 'Desenvolvedor inativado.',
            ],
        ]);
    $this->assertSoftDeleted($developer);
    $this->assertSoftDeleted($developerUser);
});

test('authenticated users can activate developers and their users', function () {
    $user          = User::factory()->create();
    $developer     = Developer::factory()->create();
    $developerUser = $developer->user;

    $developer->delete();
    $developerUser->delete();

    $response = $this->actingAs($user)->post(route('developers.activate', $developer));

    $response
        ->assertRedirect(route('developers.index', absolute: false))
        ->assertSessionHas('inertia.flash_data', [
            'toast' => [
                'type'    => 'success',
                'message' => 'Desenvolvedor ativado.',
            ],
        ]);
    $this->assertNotSoftDeleted($developer);
    $this->assertNotSoftDeleted($developerUser);
});

test('soft deleted developer users cannot authenticate', function () {
    $user          = User::factory()->create();
    $developer     = Developer::factory()->create();
    $developerUser = $developer->user;

    $this->actingAs($user)->delete(route('developers.destroy', $developer));
    auth()->logout();

    $this->post(route('login.store'), [
        'email'    => $developerUser->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});
