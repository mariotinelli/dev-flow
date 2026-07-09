<?php

declare(strict_types = 1);

namespace App\Http\Requests\System\Projects;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'key' => str($this->input('key'))->upper()->toString(),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('project')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->route('project');

        assert($project instanceof Project);

        return [
            'name'        => ['required', 'string', 'max:150'],
            'key'         => ['required', 'string', 'max:10', Rule::unique('projects', 'key')->ignore($project->id)],
            'description' => ['nullable', 'string'],
            'color'       => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'starts_at'   => ['nullable', 'date'],
            'due_at'      => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'key'   => 'identificador',
            'color' => 'cor',
        ];
    }
}
