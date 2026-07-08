<?php

declare(strict_types = 1);

use App\Actions\ProjectDocumentations\ExtractProjectDocumentationText;
use Illuminate\Support\Facades\Storage;

test('it extracts pdf text with pdftotext', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');
    fakePdftotextBinary("# PDF Section\n\nExtracted PDF text from the runbook.");

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: 'runbook.pdf',
        content: '%PDF test content',
    );

    [$text, $sourceHash] = app(ExtractProjectDocumentationText::class)->handle($projectDocumentation);

    expect($text)->toContain('PDF Section')
        ->and($text)->toContain('Extracted PDF text from the runbook.')
        ->and($sourceHash)->toHaveLength(64);
});

test('it extracts text from modern office files', function (string $filename, string $entryName) {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: $filename,
        content: officeOpenXmlWith($entryName, '<root><t># Office Section</t><t>Quarterly roadmap text</t></root>'),
    );

    [$text] = app(ExtractProjectDocumentationText::class)->handle($projectDocumentation);

    expect($text)->toContain('Office Section')
        ->and($text)->toContain('Quarterly roadmap text');
})->with([
    'docx' => ['roadmap.docx', 'word/document.xml'],
    'xlsx' => ['roadmap.xlsx', 'xl/worksheets/sheet1.xml'],
    'pptx' => ['roadmap.pptx', 'ppt/slides/slide1.xml'],
]);

test('it ignores unsupported binary formats', function (string $filename) {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: $filename,
        content: 'unsupported binary content',
    );

    [$text, $sourceHash] = app(ExtractProjectDocumentationText::class)->handle($projectDocumentation);

    expect($text)->toBeNull()
        ->and($sourceHash)->toHaveLength(64);
})->with(['legacy.doc', 'legacy.xls', 'legacy.ppt', 'archive.zip']);
