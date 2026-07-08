<?php

declare(strict_types = 1);

use App\Models\Project;
use App\Models\User;

test('authenticated users can activate projects', function () {
    $user    = User::factory()->admin()->create();
    $project = Project::factory()->trashed()->create();

    $this->actingAs($user)
        ->post(route('projects.activate', $project))
        ->assertRedirectToRoute('projects.index')
        ->assertToast('success', 'Projeto ativado.');

    expect($project->refresh()->trashed())->toBeFalse();
});
