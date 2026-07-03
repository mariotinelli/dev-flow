<?php

declare(strict_types = 1);

namespace App\Http\Requests\ProjectDocumentations;

use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Models\ProjectDocumentation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectDocumentationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProjectDocumentation::class) ?? false;
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
            'type'        => ['required', Rule::enum(ProjectDocumentationType::class)->only([ProjectDocumentationType::Link])],
            'category'    => ['required', Rule::enum(ProjectDocumentationCategory::class)],
            'url'         => ['required', 'url', 'max:255'],
        ];
    }
}
