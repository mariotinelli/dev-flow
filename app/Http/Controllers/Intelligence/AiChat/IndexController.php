<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Intelligence\AiChat;

use App\Actions\Intelligence\AiChat\GetProjectAiChatMessages;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\CurrentProject;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    public function __invoke(GetProjectAiChatMessages $getProjectAiChatMessages): Response
    {
        $this->authorize('useAiChat', Project::class);

        $project = $this->currentProject->resolve();
        $user    = request()->user();

        return Inertia::render('intelligence/ai-chat/Index', [
            'messages' => $project && $user ? $getProjectAiChatMessages->handle($project, $user) : [],
        ]);
    }
}
