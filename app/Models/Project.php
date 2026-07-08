<?php

declare(strict_types = 1);

namespace App\Models;

use App\Enums\BaseStatus;
use App\Observers\ProjectObserver;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[ObservedBy(ProjectObserver::class)]
/**
 * @property int $id
 * @property string $name
 * @property string $key
 * @property string $slug
 * @property string|null $description
 * @property string|null $color
 * @property Carbon|null $starts_at
 * @property Carbon|null $due_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'due_at'    => 'date',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(170);
    }

    /**
     * @return HasMany<ProjectRole, $this>
     */
    public function projectRoles(): HasMany
    {
        return $this->hasMany(ProjectRole::class);
    }

    /**
     * @return HasMany<ProjectMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot(['project_role_id'])
            ->withTimestamps();
    }

    public static function nextKeyForName(string $name): string
    {
        $prefix = self::keyPrefixForName($name);

        $latestSequence = self::withTrashed()
            ->where('key', 'like', "{$prefix}-%")
            ->pluck('key')
            ->map(fn (string $key): ?int => preg_match("/^{$prefix}-(\d{3})$/", $key, $matches) === 1 ? (int) $matches[1] : null)
            ->filter()
            ->max() ?? 0;

        return sprintf('%s-%03d', $prefix, $latestSequence + 1);
    }

    public static function keyPrefixForName(string $name): string
    {
        $name = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', Str::ascii($name)) ?? $name;

        $initials = collect(preg_split('/[^A-Za-z0-9]+/', $name) ?: [])
            ->filter()
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'PRJ';
    }

    #[Scope]
    public function filters(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->whereAny(['name', 'key', 'slug'], 'like', "%{$search}%"))
            ->when($filters['deleted_status'] ?? null, fn (Builder $query, string $deletedStatus) => match ($deletedStatus) {
                BaseStatus::Active->value   => $query->whereNull('deleted_at'),
                BaseStatus::Inactive->value => $query->whereNotNull('deleted_at'),
                default                     => null,
            });
    }
}
