<?php

declare(strict_types = 1);

namespace App\Models;

use App\Enums\BaseStatus;
use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $avatar_path
 * @property JobTitle $job_title
 * @property ContractType $contract_type
 * @property Seniority $seniority
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use PasskeyAuthenticatable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contract_type'           => ContractType::class,
            'seniority'               => Seniority::class,
            'job_title'               => JobTitle::class,
            'email_verified_at'       => 'datetime',
            'password'                => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    #[Scope]
    public function withoutAdmin(Builder $query): void
    {
        $query->withoutRole('admin');
    }

    #[Scope]
    public function filters(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, fn ($query, string $search) => $query->whereAny(['name', 'email'], 'like', "%{$search}%"))
            ->when($filters['role'] ?? null, fn ($query, mixed $role) => $query->whereRelation('roles', 'roles.id', (int) $role))
            ->when($filters['job_title'] ?? null, fn ($query, mixed $jobTitle) => $query->where('job_title', (int) $jobTitle))
            ->when($filters['contract_type'] ?? null, fn ($query, mixed $contractType) => $query->where('contract_type', (int) $contractType))
            ->when($filters['seniority'] ?? null, fn ($query, mixed $seniority) => $query->where('seniority', (int) $seniority))
            ->when($filters['status'] ?? null, fn ($query, string $status) => match ($status) {
                BaseStatus::Active->value   => $query->whereNull('deleted_at'),
                BaseStatus::Inactive->value => $query->whereNotNull('deleted_at'),
                default                     => null,
            });
    }
}
