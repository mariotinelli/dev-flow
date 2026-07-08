<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\DB;

test('database seeder creates sample workspace data', function () {
    $this->seed();

    $this->assertDatabaseCount('roles', 4);
    $this->assertDatabaseCount('users', 31);
    $this->assertDatabaseCount('projects', 20);
    $this->assertDatabaseCount('project_roles', 80);
    $this->assertDatabaseCount('project_members', 220);

    expect(DB::table('model_has_roles')->count())->toBe(31)
        ->and(DB::table('model_has_roles')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('users.email', 'test@example.com')
            ->where('roles.name', 'admin')
            ->exists())->toBeTrue()
        ->and(DB::table('project_members')->select('project_id')->groupBy('project_id')->havingRaw('count(*) = 11')->count())->toBe(20);
});
