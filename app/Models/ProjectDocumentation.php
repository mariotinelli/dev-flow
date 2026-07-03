<?php

declare(strict_types = 1);

namespace App\Models;

use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\Traits\HasProjectId;
use App\Models\Traits\ScopedByCurrentProject;
use App\Observers\ProjectDocumentationObserver;
use Database\Factories\ProjectDocumentationFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int $author_id
 * @property int|null $media_id
 * @property string $title
 * @property string|null $description
 * @property ProjectDocumentationType $type
 * @property ProjectDocumentationCategory $category
 * @property ProjectDocumentationVisibility $visibility
 * @property string|null $url
 * @property string|null $file_path
 * @property string|null $file_original_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[ObservedBy(ProjectDocumentationObserver::class)]
class ProjectDocumentation extends Model
{
    /** @use HasFactory<ProjectDocumentationFactory> */
    use HasFactory;
    use HasProjectId;
    use ScopedByCurrentProject;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'       => ProjectDocumentationType::class,
            'category'   => ProjectDocumentationCategory::class,
            'visibility' => ProjectDocumentationVisibility::class,
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    #[Scope]
    public function filters(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where('title', 'like', "%{$search}%"));

        $query->when($filters['type'] ?? null, fn (Builder $query, int $type) => $query->where('type', $type));
    }
}
