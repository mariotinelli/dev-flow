<?php

declare(strict_types = 1);

use App\Ai\Agents\ProjectKnowledgeAgent;
use App\Enums\Permissions\Projects\AiChatPermissions;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;

test('it streams a fake agent response for an authorized user', function () {
    [$user] = projectMemberWithAiChatPermission();

    ProjectKnowledgeAgent::fake(['Resposta baseada na base do projeto.'])->preventStrayPrompts();

    $response = $this->actingAs($user)
        ->post(route('project.ai-chat.stream'), ['question' => 'Como funciona deploy?']);

    $content = $response->streamedContent();

    $response->assertSuccessful();
    expect($content)->toContain('data:')
        ->and($content)->toContain('text_delta')
        ->and($content)->toContain('Resposta')
        ->and($content)->toContain('projeto.')
        ->and($content)->toContain('[DONE]');

    ProjectKnowledgeAgent::assertPrompted('Como funciona deploy?');
});

test('stream request requires ai chat permission', function () {
    $project = Project::factory()->create();
    $role    = ProjectRole::factory()->for($project)->create();
    $user    = User::factory()->create();

    $role->syncPermissionNames([AiChatPermissions::Use->value]);
    $role->permissions()->detach();

    ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $user->id,
        'project_role_id' => $role->id,
    ]);

    $this->actingAs($user)
        ->post(route('project.ai-chat.stream'), ['question' => 'Como funciona deploy?'])
        ->assertForbidden();
});
