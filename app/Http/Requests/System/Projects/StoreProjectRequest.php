<?php

declare(strict_types = 1);

namespace App\Http\Requests\System\Projects;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'key' => $this->boolean('key_manually_edited')
                ? str($this->input('key'))->upper()->toString()
                : Project::nextKeyForName((string) $this->input('name')),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Project::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'key'         => ['required', 'string', 'max:10', Rule::unique('projects', 'key')],
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
