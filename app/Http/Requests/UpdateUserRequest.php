<?php

declare(strict_types = 1);

namespace App\Http\Requests;

use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        assert($user instanceof User);

        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'avatar'        => ['nullable', 'image', 'max:2048'],
            'job_title'     => ['required', 'string',  Rule::in(JobTitle::values())],
            'contract_type' => ['required', 'integer', Rule::in(ContractType::values())],
            'seniority'     => ['required', 'integer', Rule::in(Seniority::values())],
        ];
    }
}
