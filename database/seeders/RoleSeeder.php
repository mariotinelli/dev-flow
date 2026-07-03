<?php

declare(strict_types = 1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::table('roles')->upsert(
            collect(['admin', 'manager', 'developer', 'viewer'])
                ->map(fn (string $role): array => [
                    'name'       => $role,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all(),
            ['name', 'guard_name'],
            ['updated_at']
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
