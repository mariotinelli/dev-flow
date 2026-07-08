<?php

declare(strict_types = 1);

namespace App\Http\Requests\ProjectRoles;

use App\Enums\Permission;
use App\Models\ProjectRole;
use App\Support\CurrentProject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRoleRequest extends FormRequest
{
    public function __construct(
        private CurrentProject $currentProject,
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null,
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProjectRole::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->currentProject->resolve();

        return [
            'name'          => ['required', 'string', 'max:255', Rule::unique('project_roles', 'name')->where('project_id', $project?->id)],
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::in(Permission::projectValues())],
        ];
    }
}
