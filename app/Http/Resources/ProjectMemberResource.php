<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProjectMember */
class ProjectMemberResource extends JsonResource
{
    /**
     * @return array{id: int, user: array{id: int, name: string, email: string}, project_role: array{id: int, name: string}, entered_at: string|null, can: array{update: bool, delete: bool}}
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'user' => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ],
            'project_role' => [
                'id'   => $this->projectRole->id,
                'name' => $this->projectRole->name,
            ],
            'entered_at' => $this->created_at?->toDateString(),
            'can'        => [
                'update' => $request->user()->can('update', $this->resource),
                'delete' => $request->user()->can('delete', $this->resource),
            ],
        ];
    }
}
