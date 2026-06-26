<?php

declare(strict_types = 1);

namespace App\Http\Requests\Users;

use App\Enums\BaseStatus;
use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search'        => ['nullable', 'string', 'max:255'],
            'role'          => ['nullable', 'integer', Rule::exists('roles', 'id')],
            'job_title'     => ['nullable', 'integer', Rule::in(JobTitle::values())],
            'contract_type' => ['nullable', 'integer', Rule::in(ContractType::values())],
            'seniority'     => ['nullable', 'integer', Rule::in(Seniority::values())],
            'status'        => ['nullable', 'string', Rule::in(BaseStatus::values())],
        ];
    }
}
