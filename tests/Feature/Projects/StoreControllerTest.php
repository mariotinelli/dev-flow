<?php

declare(strict_types = 1);

use App\Models\Project;
use App\Models\User;

test('guests are redirected when creating projects', function () {
    $this->post(route('projects.store'))->assertRedirect(route('login'));
});

test('authenticated users without permission cannot create projects', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('projects.store'))->assertForbidden();
});

test('authenticated users can create projects', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)->post(route('projects.store'), [
        'name'                => 'Dev Flow Platform',
        'key'                 => 'DFP-001',
        'key_manually_edited' => false,
        'description'         => 'Internal platform.',
        'color'               => '#1F8A70',
        'starts_at'           => '2026-07-01',
        'due_at'              => '2026-12-31',
    ])
        ->assertRedirectToRoute('projects.index')
        ->assertToast('success', 'Projeto cadastrado.');

    $project = Project::where('key', 'DFP-001')->firstOrFail();

    expect($project->name)->toBe('Dev Flow Platform')
        ->and($project->slug)->toBe('dev-flow-platform');
});

test('project creation generates sequential keys from the project name', function () {
    $user = User::factory()->admin()->create();

    Project::factory()->create(['key' => 'BC-001']);
    Project::factory()->create(['key' => 'DF-001']);

    $this->actingAs($user)->post(route('projects.store'), [
        'name'  => 'Busca Certa',
        'color' => '#1F8A70',
    ])->assertRedirectToRoute('projects.index');

    $this->actingAs($user)->post(route('projects.store'), [
        'name'  => 'DevFlow',
        'color' => '#1F8A70',
    ])->assertRedirectToRoute('projects.index');

    $this->actingAs($user)->post(route('projects.store'), [
        'name'  => 'DevFlow Mobile',
        'color' => '#1F8A70',
    ])->assertRedirectToRoute('projects.index');

    $this->actingAs($user)->post(route('projects.store'), [
        'name'  => 'API Gateway',
        'color' => '#1F8A70',
    ])->assertRedirectToRoute('projects.index');

    $this->actingAs($user)->post(route('projects.store'), [
        'name'  => 'Sistema Financeiro',
        'color' => '#1F8A70',
    ])->assertRedirectToRoute('projects.index');

    expect(Project::where('name', 'Busca Certa')->firstOrFail()->key)->toBe('BC-002')
        ->and(Project::where('name', 'DevFlow')->firstOrFail()->key)->toBe('DF-002')
        ->and(Project::where('name', 'DevFlow Mobile')->firstOrFail()->key)->toBe('DFM-001')
        ->and(Project::where('name', 'API Gateway')->firstOrFail()->key)->toBe('AG-001')
        ->and(Project::where('name', 'Sistema Financeiro')->firstOrFail()->key)->toBe('SF-001');
});

test('project creation keeps manually edited keys uppercase', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)->post(route('projects.store'), [
        'name'                => 'Busca Certa',
        'key'                 => 'busca-1',
        'key_manually_edited' => true,
        'color'               => '#1F8A70',
    ])->assertRedirectToRoute('projects.index');

    expect(Project::where('name', 'Busca Certa')->firstOrFail()->key)->toBe('BUSCA-1');
});

test('project creation validates required and unique fields', function () {
    $user = User::factory()->admin()->create();

    Project::factory()->create([
        'key' => 'DFLOW',
    ]);

    $this->actingAs($user)
        ->post(route('projects.store'), [
            'key'                 => 'dflow',
            'key_manually_edited' => true,
            'color'               => 'blue',
            'starts_at'           => '2026-12-31',
            'due_at'              => '2026-07-01',
        ])
        ->assertSessionHasErrors([
            'name',
            'key',
            'color',
            'due_at',
        ]);
});
