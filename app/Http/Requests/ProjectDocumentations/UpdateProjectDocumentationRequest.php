<?php

declare(strict_types = 1);

namespace App\Http\Requests\ProjectDocumentations;

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
            'visibility'  => ['required', Rule::enum(ProjectDocumentationVisibility::class)],
            'url'         => [
                Rule::requiredIf(fn () => (int) $this->input('type') === ProjectDocumentationType::Link->value),
                'url',
                'max:255',
            ],
            'file' => [
                Rule::requiredIf(fn () => in_array((int) $this->input('type'), [
                    ProjectDocumentationType::File->value,
                    ProjectDocumentationType::Image->value,
                ], true) && !$this->route('projectDocumentation')->file_path),
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,md,zip,rar,7z,json,xml,csv,jpg,jpeg,png,webp,gif',
                'max:102400',
            ],
        ];
    }
}
