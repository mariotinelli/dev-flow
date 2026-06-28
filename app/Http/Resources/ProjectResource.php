<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, name: string, key: string, slug: string, description: string|null, color: string|null, status: int, status_label: string, visibility: int, visibility_label: string, starts_at: string|null, due_at: string|null, is_active: bool, deleted_at: string|null, can: array{update: bool, delete: bool, restore: bool}}
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'key'              => $this->key,
            'slug'             => $this->slug,
            'description'      => $this->description,
            'color'            => $this->color,
            'status'           => $this->status->value,
            'status_label'     => $this->status->label(),
            'visibility'       => $this->visibility->value,
            'visibility_label' => $this->visibility->label(),
            'starts_at'        => $this->starts_at?->toDateString(),
            'due_at'           => $this->due_at?->toDateString(),
            'is_active'        => !$this->trashed(),
            'deleted_at'       => $this->deleted_at?->toDateTimeString(),
            'can'              => [
                'update'  => $request->user()->can('update', $this->resource),
                'delete'  => $request->user()->can('delete', $this->resource),
                'restore' => $request->user()->can('restore', $this->resource),
            ],
        ];
    }
}
