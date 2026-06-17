<?php

declare(strict_types = 1);

namespace App\Http\Requests;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Role::class) ?? false;
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255', 'not_in:admin', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::in(Permission::values())],
        ];
    }
}
