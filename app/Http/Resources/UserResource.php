<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, name: string, email: string, avatar_url: string|null, role: string, contract_type: int, contract_type_label: string, seniority: int, seniority_label: string, is_active: bool, status_label: string, can: array{update: bool, delete: bool, restore: bool}}
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'email'               => $this->email,
            'avatar_url'          => $this->avatar_path ? Storage::url($this->avatar_path) : null,
            'job_title'           => $this->job_title->value,
            'job_title_label'     => $this->job_title->label(),
            'contract_type'       => $this->contract_type->value,
            'contract_type_label' => $this->contract_type->label(),
            'seniority'           => $this->seniority->value,
            'seniority_label'     => $this->seniority->label(),
            'role'                => str($this->roles()->first()?->name ?? '')->lower()->replace('_', ' ')->ucfirst(),
            'is_active'           => !$this->trashed(),
            'status_label'        => $this->trashed() ? 'Inativo' : 'Ativo',
            'can'                 => [
                'update'  => $request->user()->can('update', $this->resource),
                'delete'  => $request->user()->can('delete', $this->resource),
                'restore' => $request->user()->can('restore', $this->resource),
            ],
        ];
    }
}
