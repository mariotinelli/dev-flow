<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Developer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeveloperSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Developer::factory()->count(20)->create();
    }
}
