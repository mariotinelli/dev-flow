<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\SettingPermissions;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
