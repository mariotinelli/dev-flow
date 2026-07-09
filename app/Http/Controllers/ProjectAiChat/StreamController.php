<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectAiChat;

use App\Ai\Agents\ProjectKnowledgeAgent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectAiChat\StreamProjectAiChatRequest;
use App\Support\CurrentProject;
use Laravel\Ai\Responses\StreamableAgentResponse;
use Laravel\Ai\Responses\StreamedAgentResponse;

class StreamController extends Controller
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    public function __invoke(StreamProjectAiChatRequest $request): StreamableAgentResponse
    {
        $validated = $request->validated();
        $project   = $this->currentProject->resolve($request);
        $user      = $request->user();

        abort_unless($project && $user, 404);

        $conversationId = $user->conversations()
            ->where('project_id', $project->id)
            ->value('id');

        $agent = $conversationId
            ? app(ProjectKnowledgeAgent::class)->continue((string) $conversationId, as: $user)
            : app(ProjectKnowledgeAgent::class)->forUser($user);

        return $agent
            ->stream((string) $validated['question'])
            ->then(function (StreamedAgentResponse $response) use ($project, $user): void {
                if (!$response->conversationId) {
                    return;
                }

                $user->conversations()
                    ->where('id', $response->conversationId)
                    ->whereNull('project_id')
                    ->update(['project_id' => $project->id]);
            });
    }
}
