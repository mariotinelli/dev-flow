<?php

declare(strict_types = 1);

use App\Jobs\GenerateProjectKnowledgeJob;
use App\Models\ProjectKnowledgeChunk;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Embeddings;

test('it generates project knowledge for a project documentation', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');
    Embeddings::fake();

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: 'runbook.md',
        content: "# Deployments\n\nDeployments run through the release pipeline.",
    );

    app()->call([(new GenerateProjectKnowledgeJob($projectDocumentation->id)), 'handle']);

    expect(ProjectKnowledgeChunk::query()->count())->toBe(1);
});
