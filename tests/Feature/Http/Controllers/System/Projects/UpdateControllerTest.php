<?php

declare(strict_types = 1);

use App\Models\Project;
use App\Models\User;

test('authenticated users can update projects', function () {
    $user    = User::factory()->admin()->create();
    $project = Project::factory()->create();

    $this->actingAs($user)->post(route('system.projects.update', $project), [
        'name'        => 'Updated Project',
        'key'         => 'UPD',
        'description' => 'Updated description.',
        'color'       => '#2563EB',
        'starts_at'   => '2026-07-01',
        'due_at'      => '2026-08-01',
    ])
        ->assertRedirectToRoute('system.projects.index')
        ->assertToast('success', 'Projeto atualizado.');

    $project->refresh();

    expect($project->name)->toBe('Updated Project')
        ->and($project->key)->toBe('UPD')
        ->and($project->slug)->toBe('updated-project');
});

test('project update validates uniqueness', function () {
    $user    = User::factory()->admin()->create();
    $project = Project::factory()->create();
    $other   = Project::factory()->create([
        'key' => 'OTHER',
    ]);

    $this->actingAs($user)->post(route('system.projects.update', $project), [
        'name' => 'Updated Project',
        'key'  => $other->key,
    ])
        ->assertSessionHasErrors([
            'key',
        ]);
});
