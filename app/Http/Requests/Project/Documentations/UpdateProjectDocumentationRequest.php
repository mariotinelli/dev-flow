<?php

declare(strict_types = 1);

namespace App\Http\Requests\Project\Documentations;

use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectDocumentationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('projectDocumentation')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'type'        => ['required', Rule::enum(ProjectDocumentationType::class)],
            'category'    => ['required', Rule::enum(ProjectDocumentationCategory::class)],
            'visibility'  => ['required', Rule::in($this->allowedVisibilityValues())],
            'url'         => Rule::when(
                fn () => (int) $this->input('type') === ProjectDocumentationType::Link->value,
                ['required', 'url', 'max:255'],
            ),
            'file' => Rule::when(
                fn () => in_array((int) $this->input('type'), [
                    ProjectDocumentationType::File->value,
                    ProjectDocumentationType::Image->value,
                ], true) && !$this->route('projectDocumentation')->file_path,
                ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,md,zip,rar,7z,json,xml,csv,jpg,jpeg,png,webp,gif', 'max:102400'],
            ),
        ];
    }

    /**
     * @return list<int>
     */
    private function allowedVisibilityValues(): array
    {
        return collect(ProjectDocumentationVisibility::cases())
            ->reject(
                fn (ProjectDocumentationVisibility $visibility): bool => !$this->user()?->hasRole('admin')
                    && $visibility === ProjectDocumentationVisibility::AdministratorsOnly,
            )
            ->map(fn (ProjectDocumentationVisibility $visibility): int => $visibility->value)
            ->values()
            ->all();
    }
}
