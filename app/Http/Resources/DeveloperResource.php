<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use App\Models\Developer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Developer */
class DeveloperResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, name: string, email: string, avatar_url: string|null, role: string, contract_type: int, contract_type_label: string, seniority: int, seniority_label: string, is_active: bool, status_label: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->user->name,
            'email'               => $this->user->email,
            'avatar_url'          => $this->avatar_path ? Storage::url($this->avatar_path) : null,
            'role'                => $this->role,
            'contract_type'       => $this->contract_type->value,
            'contract_type_label' => $this->contract_type->label(),
            'seniority'           => $this->seniority->value,
            'seniority_label'     => $this->seniority->label(),
            'is_active'           => !$this->trashed(),
            'status_label'        => $this->trashed() ? 'Inativo' : 'Ativo',
        ];
    }
}
