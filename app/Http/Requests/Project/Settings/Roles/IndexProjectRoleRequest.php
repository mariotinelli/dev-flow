<?php

declare(strict_types = 1);

namespace App\Http\Requests\Project\Settings\Roles;

use App\Enums\BaseStatus;
use App\Models\ProjectRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProjectRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', ProjectRole::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search'         => ['nullable', 'string', 'max:255'],
            'deleted_status' => ['nullable', 'string', Rule::in(BaseStatus::values())],
        ];
    }
}
