<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $role = Role::findOrCreate('developer');

        User::factory()
            ->withRole($role->name)
            ->count(20)
            ->create();
    }
}
