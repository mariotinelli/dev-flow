<?php

declare(strict_types = 1);

use App\Models\Project;
use App\Models\User;

test('authenticated users can inactivate projects', function () {
    $user    = User::factory()->admin()->create();
    $project = Project::factory()->create();

    $this->actingAs($user)
        ->delete(route('system.projects.destroy', $project))
        ->assertRedirectToRoute('system.projects.index')
        ->assertToast('success', 'Projeto inativado.');

    $this->assertSoftDeleted($project);
});
