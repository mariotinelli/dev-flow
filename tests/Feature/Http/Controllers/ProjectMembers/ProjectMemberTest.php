<?php

declare(strict_types = 1);

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;

test('project members connect projects users and project roles', function () {
    $project     = Project::factory()->create();
    $user        = User::factory()->create();
    $projectRole = ProjectRole::factory()->for($project)->create();

    $member = ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $user->id,
        'project_role_id' => $projectRole->id,
    ]);

    expect($member->project->is($project))->toBeTrue()
        ->and($member->user->is($user))->toBeTrue()
        ->and($member->projectRole->is($projectRole))->toBeTrue()
        ->and($project->users()->first()->is($user))->toBeTrue()
        ->and($user->projects()->first()->is($project))->toBeTrue()
        ->and($projectRole->members()->first()->is($member))->toBeTrue();
});

test('deleted project members are removed from project users without deleting the user', function () {
    $project     = Project::factory()->create();
    $user        = User::factory()->create();
    $projectRole = ProjectRole::factory()->for($project)->create();

    $member = ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $user->id,
        'project_role_id' => $projectRole->id,
    ]);

    $member->delete();

    expect($project->users)->toBeEmpty()
        ->and($user->projects)->toBeEmpty()
        ->and($user->exists())->toBeTrue();
});
