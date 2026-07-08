<?php

declare(strict_types = 1);

use App\Models\Project;
use Illuminate\Database\QueryException;

test('projects can be created with the expected fields', function () {
    $project = Project::factory()->create([
        'name'        => 'Dev Flow Platform',
        'key'         => 'DFLOW',
        'description' => 'Internal project management platform.',
        'color'       => '#1F8A70',
        'starts_at'   => '2026-07-01',
        'due_at'      => '2026-12-31',
    ]);

    $this->assertModelExists($project);

    expect($project)
        ->name->toBe('Dev Flow Platform')
        ->key->toBe('DFLOW')
        ->slug->toBe('dev-flow-platform')
        ->description->toBe('Internal project management platform.')
        ->color->toBe('#1F8A70');
});

test('project date attributes are cast to dates', function () {
    $project = Project::factory()->create([
        'starts_at' => '2026-07-01',
        'due_at'    => '2026-12-31',
    ])->refresh();

    expect($project->starts_at?->toDateString())->toBe('2026-07-01')
        ->and($project->due_at?->toDateString())->toBe('2026-12-31');
});

test('projects are soft deleted', function () {
    $project = Project::factory()->create();

    $project->delete();

    $this->assertSoftDeleted($project);
    expect(Project::query()->find($project->id))->toBeNull()
        ->and(Project::withTrashed()->find($project->id))->not->toBeNull();
});

test('project keys must be unique', function () {
    Project::factory()->create([
        'key' => 'DFLOW',
    ]);

    expect(fn () => Project::factory()->create([
        'key' => 'DFLOW',
    ]))->toThrow(QueryException::class);
});

test('project keys can be generated from project names', function (string $name, string $key) {
    expect(Project::nextKeyForName($name))->toBe($key);
})->with([
    ['Busca Certa', 'BC-001'],
    ['DevFlow', 'DF-001'],
    ['DevFlow Mobile', 'DFM-001'],
    ['API Gateway', 'AG-001'],
    ['Sistema Financeiro', 'SF-001'],
]);

test('project key generation increments existing prefixes', function () {
    Project::factory()->create(['key' => 'BC-001']);
    Project::factory()->create(['key' => 'BC-002']);

    expect(Project::nextKeyForName('Busca Certa'))->toBe('BC-003');
});

test('project slugs are generated uniquely from the name', function () {
    $firstProject  = Project::factory()->create(['name' => 'Dev Flow Platform']);
    $secondProject = Project::factory()->create(['name' => 'Dev Flow Platform']);

    expect($firstProject->slug)->toBe('dev-flow-platform')
        ->and($secondProject->slug)->toBe('dev-flow-platform-1');
});
