<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $now      = now();
        $password = Hash::make('password');

        $userRows = collect([
            [
                'name'                      => 'Test User',
                'email'                     => 'test@example.com',
                'email_verified_at'         => $now,
                'password'                  => $password,
                'avatar_path'               => null,
                'job_title'                 => JobTitle::FullStackDeveloper->value,
                'contract_type'             => ContractType::Fixed->value,
                'seniority'                 => Seniority::Lead->value,
                'remember_token'            => Str::random(10),
                'two_factor_secret'         => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at'   => null,
                'deleted_at'                => null,
                'created_at'                => $now,
                'updated_at'                => $now,
            ],
        ])->merge(collect(range(1, 30))->map(fn (int $index): array => [
            'name'                      => sprintf('User %02d', $index),
            'email'                     => sprintf('user%02d@devflow.test', $index),
            'email_verified_at'         => $now,
            'password'                  => $password,
            'avatar_path'               => null,
            'job_title'                 => $this->valueFor(JobTitle::values(), $index),
            'contract_type'             => $this->valueFor(ContractType::values(), $index),
            'seniority'                 => $this->valueFor(Seniority::values(), $index),
            'remember_token'            => Str::random(10),
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
            'deleted_at'                => null,
            'created_at'                => $now,
            'updated_at'                => $now,
        ]));

        DB::table('users')->upsert(
            $userRows->all(),
            ['email'],
            ['name', 'email_verified_at', 'password', 'avatar_path', 'job_title', 'contract_type', 'seniority', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at', 'deleted_at', 'updated_at']
        );

        $roleIds = DB::table('roles')
            ->whereIn('name', ['admin', 'manager', 'developer', 'viewer'])
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        $users = DB::table('users')
            ->whereIn('email', $userRows->pluck('email'))
            ->orderBy('email')
            ->pluck('id', 'email');

        DB::table('model_has_roles')->upsert(
            $users->map(function (int $userId, string $email) use ($roleIds): array {
                $roleName = $email === 'test@example.com' ? 'admin' : collect(['admin', 'manager', 'developer', 'viewer'])
                    ->sortBy(fn (string $role): int => crc32("{$email}-{$role}"))
                    ->first();

                return [
                    'role_id'    => (int) $roleIds[$roleName],
                    'model_type' => User::class,
                    'model_id'   => $userId,
                ];
            })->values()->all(),
            ['role_id', 'model_type', 'model_id'],
            ['role_id']
        );
    }

    /**
     * @template TValue
     *
     * @param  list<TValue>  $values
     * @return TValue
     */
    private function valueFor(array $values, int $index): mixed
    {
        return $values[($index - 1) % count($values)];
    }
}
