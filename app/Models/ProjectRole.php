<?php

declare(strict_types = 1);

namespace App\Models;

use App\Enums\BaseStatus;
use App\Models\Traits\HasProjectId;
use App\Models\Traits\ScopedByCurrentProject;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;

/**
 * @property int $id
 * @property int $project_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ProjectRole extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectRoleFactory> */
    use HasFactory;
    use HasProjectId;
    use ScopedByCurrentProject;
    use SoftDeletes;

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return HasMany<ProjectMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'project_role_permissions');
    }

    /**
     * @param  list<string>  $permissionNames
     */
    public function syncPermissionNames(array $permissionNames): void
    {
        $permissionIds = Permission::query()
            ->whereIn('name', $permissionNames)
            ->pluck('id')
            ->all();

        $this->permissions()->sync($permissionIds);
    }

    #[Scope]
    public function filters(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($filters['deleted_status'] ?? null, fn (Builder $query, string $deletedStatus) => match ($deletedStatus) {
                BaseStatus::Active->value   => $query->whereNull('deleted_at'),
                BaseStatus::Inactive->value => $query->whereNotNull('deleted_at'),
                default                     => null,
            });
    }
}
