<?php

declare(strict_types = 1);

namespace App\Http\Requests\ProjectRoles;

use App\Enums\Permission;
use App\Models\ProjectRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('projectRole')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectRole = $this->route('projectRole');

        assert($projectRole instanceof ProjectRole);

        return [
            'name'          => ['required', 'string', 'max:255', Rule::unique('project_roles', 'name')->where('project_id', $projectRole->project_id)->ignore($projectRole->id)],
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::in(Permission::projectValues())],
        ];
    }
}
