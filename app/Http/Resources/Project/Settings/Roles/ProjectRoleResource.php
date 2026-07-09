<?php

declare(strict_types = 1);

namespace App\Http\Resources\Project\Settings\Roles;

use App\Models\ProjectRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProjectRole */
class ProjectRoleResource extends JsonResource
{
    /**
     * @return array{id: int, name: string, permissions_count: int, is_active: bool, deleted_at: string|null, can: array{update: bool, delete: bool, restore: bool}}
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'permissions_count' => $this->permissions_count,
            'is_active'         => !$this->trashed(),
            'deleted_at'        => $this->deleted_at?->toDateTimeString(),
            'can'               => [
                'update'  => $request->user()->can('update', $this->resource),
                'delete'  => $request->user()->can('delete', $this->resource),
                'restore' => $request->user()->can('restore', $this->resource),
            ],
        ];
    }
}
