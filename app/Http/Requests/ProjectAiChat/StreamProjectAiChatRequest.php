<?php

declare(strict_types = 1);

namespace App\Http\Requests\ProjectAiChat;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StreamProjectAiChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('useAiChat', Project::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:4000'],
        ];
    }
}
