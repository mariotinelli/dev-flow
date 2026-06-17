<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Enums\Permission as SystemPermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (SystemPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('admin', 'web')->syncPermissions(SystemPermission::values());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
