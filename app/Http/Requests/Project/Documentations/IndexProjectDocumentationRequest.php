<?php

declare(strict_types = 1);

namespace App\Http\Requests\Project\Documentations;

use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\ProjectDocumentation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexProjectDocumentationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', ProjectDocumentation::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search'     => ['nullable', 'string', 'max:255'],
            'type'       => ['nullable', 'int', 'in:' . implode(',', ProjectDocumentationType::values())],
            'category'   => ['nullable', 'int', 'in:' . implode(',', ProjectDocumentationCategory::values())],
            'visibility' => ['nullable', 'int', 'in:' . implode(',', ProjectDocumentationVisibility::values())],
        ];
    }
}
