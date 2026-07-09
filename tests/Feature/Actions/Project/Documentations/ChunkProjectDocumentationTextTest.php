<?php

declare(strict_types = 1);

use App\Actions\Project\Documentations\ChunkProjectDocumentationText;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\ProjectDocumentation;

test('it preserves document category and section context in embedded chunks', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Security Guide',
        'type'       => ProjectDocumentationType::File,
        'category'   => ProjectDocumentationCategory::Architecture,
        'visibility' => ProjectDocumentationVisibility::ProjectMembers,
        'url'        => null,
    ]);

    $chunks = app(ChunkProjectDocumentationText::class)->handle($projectDocumentation, <<<'MARKDOWN'
        # Authentication

        Users authenticate with email and password.

        ## Two factor

        Recovery codes are required before disabling two factor authentication.
    MARKDOWN);

    expect($chunks)->toHaveCount(2)
        ->and($chunks[0]['section_path'])->toBe('Authentication')
        ->and($chunks[0]['embedded_content'])->toContain('Documento: Security Guide')
        ->and($chunks[0]['embedded_content'])->toContain('Categoria: Architecture')
        ->and($chunks[0]['embedded_content'])->toContain('Seção: Authentication')
        ->and($chunks[1]['section_path'])->toBe('Two factor')
        ->and($chunks[0]['content_hash'])->toHaveLength(64);
});
