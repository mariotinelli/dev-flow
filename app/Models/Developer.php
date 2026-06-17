<?php

declare(strict_types = 1);

namespace App\Models;

use App\Enums\ContractType;
use App\Enums\Seniority;
use Database\Factories\DeveloperFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $avatar_path
 * @property string $role
 * @property ContractType $contract_type
 * @property Seniority $seniority
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Developer extends Model
{
    /** @use HasFactory<DeveloperFactory> */
    use HasFactory;
    use SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'contract_type' => ContractType::class,
            'seniority'     => Seniority::class,
        ];
    }
}
