<?php

declare(strict_types = 1);

use App\Actions\Intelligence\AiChat\GetProjectAiChatMessages;
use App\Ai\Agents\ProjectKnowledgeAgent;
use Illuminate\Support\Facades\DB;

test('it returns no messages when the project has no saved conversation', function () {
    [$user, $project] = projectMemberWithAiChatPermission();

    $messages = app(GetProjectAiChatMessages::class)->handle($project, $user);

    expect($messages)->toBe([]);
});

test('it loads previous project chat messages from the saved conversation', function () {
    [$user, $project] = projectMemberWithAiChatPermission();

    $conversationId = (string) str()->uuid();

    DB::table('agent_conversations')->insert([
        'id'         => $conversationId,
        'user_id'    => $user->id,
        'project_id' => $project->id,
        'title'      => 'Custos AWS',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('agent_conversation_messages')->insert([
        [
            'id'              => (string) str()->uuid(),
            'conversation_id' => $conversationId,
            'user_id'         => $user->id,
            'agent'           => ProjectKnowledgeAgent::class,
            'role'            => 'user',
            'content'         => 'Quais são os custos mensais da AWS?',
            'attachments'     => '[]',
            'tool_calls'      => '[]',
            'tool_results'    => '[]',
            'usage'           => '[]',
            'meta'            => '[]',
            'created_at'      => now()->subSecond(),
            'updated_at'      => now()->subSecond(),
        ],
        [
            'id'              => (string) str()->uuid(),
            'conversation_id' => $conversationId,
            'user_id'         => $user->id,
            'agent'           => ProjectKnowledgeAgent::class,
            'role'            => 'assistant',
            'content'         => 'Os custos mensais da AWS estão documentados na fonte principal.',
            'attachments'     => '[]',
            'tool_calls'      => '[]',
            'tool_results'    => json_encode([[
                'id'        => 'tool-result-id',
                'name'      => 'SearchProjectKnowledgeTool',
                'arguments' => ['question' => 'Quais são os custos mensais da AWS?'],
                'result'    => json_encode([
                    'sources' => [[
                        'id'          => 10,
                        'chunk_id'    => 20,
                        'title'       => 'Embedding 6',
                        'source_type' => 'documentation',
                        'source_id'   => 30,
                        'position'    => 0,
                        'score'       => 0.57,
                        'metadata'    => [],
                    ]],
                ]),
            ]]),
            'usage'      => '[]',
            'meta'       => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $messages = app(GetProjectAiChatMessages::class)->handle($project, $user);

    expect($messages)->toHaveCount(2)
        ->and($messages[0]['content'])->toBe('Quais são os custos mensais da AWS?')
        ->and($messages[1]['content'])->toBe('Os custos mensais da AWS estão documentados na fonte principal.')
        ->and($messages[1]['sources'][0]['title'])->toBe('Embedding 6');
});
