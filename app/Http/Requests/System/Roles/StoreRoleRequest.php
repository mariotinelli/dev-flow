<?php

declare(strict_types = 1);

namespace App\Http\Requests\System\Roles;

use App\Enums\Permission;
use App\Rules\NotReservedRoleName;
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
            'name' => [
                'required',
                'string',
                'max:255',
                new NotReservedRoleName(),
                Rule::unique('roles', 'name')->where('guard_name', 'web'),
            ],
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::in(Permission::systemValues())],
        ];
    }
}
