<?php

declare(strict_types = 1);

namespace App\Http\Requests\System\Projects;

use App\Enums\BaseStatus;
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
            'deleted_status' => ['nullable', 'string', Rule::in(BaseStatus::values())],
        ];
    }
}
