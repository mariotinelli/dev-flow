<?php

declare(strict_types = 1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectMemberSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $users = DB::table('users')
            ->whereIn('email', collect(range(1, 30))->map(fn (int $index): string => sprintf('user%02d@devflow.test', $index)))
            ->orderBy('email')
            ->pluck('id');

        $projects = DB::table('projects')
            ->whereIn('key', $this->projectKeys())
            ->orderBy('key')
            ->pluck('id', 'key');

        $projectRoles = DB::table('project_roles')
            ->whereIn('project_id', $projects->values())
            ->get(['id', 'project_id', 'name'])
            ->groupBy('project_id');

        DB::table('project_members')->upsert(
            $projects->flatMap(function (int $projectId, string $projectKey) use ($now, $projectRoles, $users): array {
                $projectUserIds = $users
                    ->sortBy(fn (int $userId): int => crc32("{$projectKey}-{$userId}"))
                    ->take(11)
                    ->values();

                $roles = $projectRoles->get($projectId, collect())->values();

                return $projectUserIds->map(function (int $userId, int $index) use ($now, $projectId, $projectKey, $roles): array {
                    $role = $roles->sortBy(fn (object $role): int => crc32("{$projectKey}-{$userId}-{$role->name}"))->values()->get($index % $roles->count());

                    return [
                        'project_id'      => $projectId,
                        'user_id'         => $userId,
                        'project_role_id' => $role->id,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];
                })->all();
            })->values()->all(),
            ['project_id', 'user_id'],
            ['project_role_id', 'updated_at']
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
