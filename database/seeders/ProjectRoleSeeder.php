<?php

declare(strict_types = 1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectRoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $projects = DB::table('projects')
            ->whereIn('key', $this->projectKeys())
            ->pluck('id');

        DB::table('project_roles')->upsert(
            $projects->flatMap(fn (int $projectId): array => collect(['Owner', 'Manager', 'Developer', 'Viewer'])
                ->map(fn (string $role): array => [
                    'project_id' => $projectId,
                    'name'       => $role,
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all())->values()->all(),
            ['project_id', 'name'],
            ['deleted_at', 'updated_at']
        );
    }

    /**
     * @return list<string>
     */
    private function projectKeys(): array
    {
        return ['DFP-001', 'CP-001', 'MC-001', 'AH-001', 'BE-001', 'SD-001', 'KB-001', 'AG-001', 'AS-001', 'DS-001', 'NC-001', 'SS-001', 'AT-001', 'OF-001', 'IH-001', 'DP-001', 'SC-001', 'RB-001', 'QL-001', 'RT-001'];
    }
}
