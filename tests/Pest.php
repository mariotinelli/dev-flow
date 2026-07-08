<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\DocumentPermissions;
use App\Enums\Permissions\Projects\MemberPermissions;
use App\Enums\Permissions\Projects\SettingPermissions;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\Project;
use App\Models\ProjectDocumentation;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

TestResponse::macro('assertToast', function (string $type, string $message): TestResponse {
    $this->assertSessionHas('inertia.flash_data');

    $flashData = session('inertia.flash_data');

    expect(data_get($flashData, 'toast.type'))->toBe($type);
    expect(data_get($flashData, 'toast.message'))->toBe($message);

    return $this;
});

/**
 * @return array{0: User, 1: Project, 2: ProjectRole}
 */
function projectMemberWithSettingsPermission(): array
{
    $project = Project::factory()->create();
    $role    = ProjectRole::factory()->for($project)->create();
    $user    = User::factory()->create();

    $role->syncPermissionNames([SettingPermissions::Manage->value]);

    ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $user->id,
        'project_role_id' => $role->id,
    ]);

    return [$user, $project, $role];
}

/**
 * @return array{0: User, 1: Project, 2: ProjectRole}
 */
function projectMemberWithMemberPermissions(MemberPermissions ...$permissions): array
{
    $project = Project::factory()->create();
    $role    = ProjectRole::factory()->for($project)->create();
    $user    = User::factory()->create();

    $role->syncPermissionNames(array_map(fn (MemberPermissions $permission): string => $permission->value, $permissions));

    ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $user->id,
        'project_role_id' => $role->id,
    ]);

    return [$user, $project, $role];
}

/**
 * @return array{0: User, 1: Project, 2: ProjectRole}
 */
function projectMemberWithDocumentPermissions(DocumentPermissions ...$permissions): array
{
    $project = Project::factory()->create();
    $role    = ProjectRole::factory()->for($project)->create();
    $user    = User::factory()->create();

    $role->syncPermissionNames(array_map(fn (DocumentPermissions $permission): string => $permission->value, $permissions));

    ProjectMember::factory()->create([
        'project_id'      => $project->id,
        'user_id'         => $user->id,
        'project_role_id' => $role->id,
    ]);

    return [$user, $project, $role];
}

function createFileProjectDocumentationWithContent(
    int $userId,
    int $projectId,
    string $filename,
    string $content,
    ProjectDocumentationVisibility $visibility = ProjectDocumentationVisibility::ProjectMembers,
): ProjectDocumentation {
    $filePath = UploadedFile::fake()->createWithContent($filename, $content)->store('project-documentations', 's3');

    return ProjectDocumentation::factory()->create([
        'project_id'         => $projectId,
        'author_id'          => $userId,
        'title'              => 'Project documentation',
        'type'               => ProjectDocumentationType::File,
        'category'           => ProjectDocumentationCategory::Architecture,
        'visibility'         => $visibility,
        'url'                => null,
        'file_path'          => $filePath,
        'file_original_name' => $filename,
    ]);
}

function fakePdftotextBinary(string $text): void
{
    $directory = sys_get_temp_dir() . '/fake-pdftotext-' . uniqid();

    mkdir($directory);

    $binary = $directory . '/pdftotext';
    file_put_contents($binary, "#!/bin/sh\nprintf '%s' " . escapeshellarg($text) . ' > "$3"' . "\n");
    chmod($binary, 0755);

    putenv('PATH=' . $directory . PATH_SEPARATOR . getenv('PATH'));
}

function officeOpenXmlWith(string $entryName, string $xml): string
{
    $path = tempnam(sys_get_temp_dir(), 'office-open-xml-');
    $zip  = new ZipArchive();

    $zip->open($path, ZipArchive::OVERWRITE);
    $zip->addFromString($entryName, $xml);
    $zip->close();

    $contents = file_get_contents($path);

    unlink($path);

    return $contents ?: '';
}
