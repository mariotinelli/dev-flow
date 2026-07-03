<?php

declare(strict_types = 1);

namespace App\Http\Requests\ProjectDocumentations;

use App\Enums\ProjectDocumentationType;
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
            'search' => ['nullable', 'string', 'max:255'],
            'type'   => ['nullable', 'int', 'in:' . implode(',', ProjectDocumentationType::values())],
        ];
    }
}
