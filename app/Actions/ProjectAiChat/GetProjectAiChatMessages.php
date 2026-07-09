<?php

declare(strict_types = 1);

namespace App\Actions\ProjectAiChat;

use App\Models\Project;
use App\Models\User;

class GetProjectAiChatMessages
{
    /**
     * @return list<array{id: string, role: string, content: string, sources: list<array<string, mixed>>}>
     */
    public function handle(Project $project, User $user): array
    {
        $conversation = $user->conversations()
            ->where('project_id', $project->id)
            ->with(['messages' => fn ($query) => $query
                ->whereIn('role', ['user', 'assistant'])
                ->orderBy('created_at')
                ->orderBy('id')])
            ->latest('updated_at')
            ->first();

        if (!$conversation) {
            return [];
        }

        return $conversation->messages
            ->map(fn (object $message): array => [
                'id'      => (string) $message->id,
                'role'    => (string) $message->role,
                'content' => (string) $message->content,
                'sources' => $message->role === 'assistant' ? $this->sourcesFromToolResults($message->tool_results) : [],
            ])
            ->filter(fn (array $message): bool => $message['content'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sourcesFromToolResults(mixed $toolResults): array
    {
        $decoded = is_string($toolResults) ? json_decode($toolResults, true) : $toolResults;

        if (!is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->flatMap(function (array $toolResult): array {
                $result = $toolResult['result'] ?? null;

                if (!is_string($result)) {
                    return [];
                }

                $decodedResult = json_decode($result, true);

                return is_array($decodedResult) && is_array($decodedResult['sources'] ?? null)
                    ? $decodedResult['sources']
                    : [];
            })
            ->unique('id')
            ->values()
            ->all();
    }
}
