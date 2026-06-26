<?php

declare(strict_types = 1);

use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('authenticated users can update developers', function () {
    $user      = User::factory()->admin()->create();
    $role      = Role::create(['name' => 'developer']);
    $otherUser = User::factory()->create([
        'job_title'     => JobTitle::BackendDeveloper,
        'contract_type' => ContractType::Freelance,
        'seniority'     => Seniority::Junior,
    ]);

    $this->actingAs($user)->post(route('users.update', $otherUser), [
        'name'          => 'Updated Name',
        'email'         => 'updated@example.com',
        'job_title'     => JobTitle::BackendDeveloper->value,
        'contract_type' => ContractType::Fixed->value,
        'seniority'     => Seniority::Lead->value,
        'role_id'       => $role->id,
    ])
        ->assertRedirectToRoute('users.index')
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
