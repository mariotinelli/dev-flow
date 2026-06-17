<?php

declare(strict_types = 1);

namespace App\Http\Requests;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('role')) ?? false;
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');

        assert($role instanceof Role);

        return [
            'name'          => ['required', 'string', 'max:255', 'not_in:admin', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role->id)],
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::in(Permission::values())],
        ];
    }
}
