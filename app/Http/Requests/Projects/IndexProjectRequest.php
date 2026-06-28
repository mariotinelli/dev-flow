<?php

declare(strict_types = 1);

namespace App\Http\Requests\Projects;

use App\Enums\BaseStatus;
use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Project::class) ?? false;
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
            'status'         => ['nullable', 'integer', Rule::in(ProjectStatus::values())],
            'visibility'     => ['nullable', 'integer', Rule::in(ProjectVisibility::values())],
            'deleted_status' => ['nullable', 'string', Rule::in(BaseStatus::values())],
        ];
    }
}
