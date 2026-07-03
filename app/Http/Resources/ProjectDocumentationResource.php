<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use App\Models\ProjectDocumentation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProjectDocumentation */
class ProjectDocumentationResource extends JsonResource
{
    /**
     * @return array{id: int, title: string, description: string|null, type: string, type_label: string, category: string, category_label: string, url: string|null, author: array{id: int, name: string}, created_at: string|null, can: array{update: bool, delete: bool}}
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'description'    => $this->description,
            'type'           => $this->type->value,
            'type_label'     => $this->type->label(),
            'category'       => $this->category->value,
            'category_label' => $this->category->label(),
            'url'            => $this->url,
            'author'         => [
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ],
            'created_at' => $this->created_at?->toDateString(),
            'can'        => [
                'update' => $request->user()->can('update', $this->resource),
                'delete' => $request->user()->can('delete', $this->resource),
            ],
        ];
    }
}
