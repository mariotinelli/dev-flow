<?php

declare(strict_types = 1);

namespace App\Http\Resources\Project\Documentations;

use App\Enums\ProjectDocumentationType;
use App\Models\ProjectDocumentation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin ProjectDocumentation */
class ProjectDocumentationResource extends JsonResource
{
    /**
     * @return array{id: int, title: string, description: string|null, type: int, type_label: string, category: int, category_label: string, visibility: int, visibility_label: string, url: string|null, file_original_name: string|null, download_url: string|null, preview_url: string|null, author: array{id: int, name: string}, created_at: string|null, can: array{update: bool, delete: bool}}
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'description'        => $this->description,
            'type'               => $this->type->value,
            'type_label'         => $this->type->label(),
            'category'           => $this->category->value,
            'category_label'     => $this->category->label(),
            'visibility'         => $this->visibility->value,
            'visibility_label'   => $this->visibility->label(),
            'url'                => $this->url,
            'file_original_name' => $this->file_original_name,
            'download_url'       => in_array($this->type, [ProjectDocumentationType::File, ProjectDocumentationType::Image], true) && $this->file_path
                ? Storage::disk('s3')->temporaryUrl($this->file_path, now()->addMinutes(5))
                : null,
            'preview_url' => $this->type === ProjectDocumentationType::Image && $this->file_path
                ? route('project.documentations.download', [$this->resource, 'inline' => 1])
                : null,
            'author' => [
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ],
            'created_at' => $this->created_at?->format('d/m/Y'),
            'can'        => [
                'update' => $request->user()->can('update', $this->resource),
                'delete' => $request->user()->can('delete', $this->resource),
            ],
        ];
    }
}
