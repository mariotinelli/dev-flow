<?php

declare(strict_types = 1);

use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('guests are redirected when updating users', function () {
    $user = User::factory()->create();

    $this->post(route('system.users.update', $user))->assertRedirect(route('login'));
});

test('authenticated users without permission cannot update users', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create([
        'email' => 'unchanged@example.com',
    ]);

    $this->actingAs($user)
        ->post(route('system.users.update', $otherUser), [
            'name'  => 'Unauthorized Update',
            'email' => 'unauthorized@example.com',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('users', [
        'id'    => $otherUser->id,
        'email' => 'unchanged@example.com',
    ]);
});

test('authenticated users can update developers', function () {
    $user      = User::factory()->admin()->create();
    $role      = Role::create(['name' => 'developer']);
    $otherUser = User::factory()->create([
        'job_title'     => JobTitle::BackendDeveloper,
        'contract_type' => ContractType::Freelance,
        'seniority'     => Seniority::Junior,
    ]);

    $this->actingAs($user)->post(route('system.users.update', $otherUser), [
        'name'          => 'Updated Name',
        'email'         => 'updated@example.com',
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
        'role_id'       => $role->id,
    ])
        ->assertRedirectToRoute('system.users.index')
        ->assertToast('success', 'Usuário atualizado.');

    $this->assertDatabaseHas('users', [
        'id'            => $otherUser->id,
        'name'          => 'Updated Name',
        'email'         => 'updated@example.com',
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
    ]);

    expect($otherUser->refresh()->hasRole($role))->toBeTrue();
});

test('user email can keep its current value when updating', function () {
    $user      = User::factory()->admin()->create();
    $role      = Role::create(['name' => 'same-email-role']);
    $otherUser = User::factory()->create([
        'email' => 'same@example.com',
    ]);

    $this->actingAs($user)->post(route('system.users.update', $otherUser), [
        'name'          => 'Same Email User',
        'email'         => 'same@example.com',
        'job_title'     => JobTitle::FrontendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Mid->value,
        'role_id'       => $role->id,
    ])
        ->assertRedirectToRoute('system.users.index');

    $this->assertDatabaseHas('users', [
        'id'    => $otherUser->id,
        'email' => 'same@example.com',
    ]);
});

test('user email must be unique when updating', function () {
    $user         = User::factory()->admin()->create();
    $role         = Role::create(['name' => 'unique-update-user-role']);
    $existingUser = User::factory()->create();
    $otherUser    = User::factory()->create();

    $this->actingAs($user)->post(route('system.users.update', $otherUser), [
        'name'          => 'Duplicated Email User',
        'email'         => $existingUser->email,
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
        'role_id'       => $role->id,
    ])
        ->assertSessionHasErrors(['email']);
});

test('users cannot be updated with the admin role', function () {
    $user      = User::factory()->admin()->create();
    $adminRole = Role::findByName('admin');
    $otherUser = User::factory()->create([
        'email' => 'editable@example.com',
    ]);

    $this->actingAs($user)->post(route('system.users.update', $otherUser), [
        'name'          => 'Admin Role User',
        'email'         => 'admin-role@example.com',
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
        'role_id'       => $adminRole->id,
    ])
        ->assertSessionHasErrors(['role_id']);

    $this->assertDatabaseHas('users', [
        'id'    => $otherUser->id,
        'email' => 'editable@example.com',
    ]);
});
