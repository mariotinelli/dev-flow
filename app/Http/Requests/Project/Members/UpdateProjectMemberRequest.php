<?php

declare(strict_types = 1);

namespace App\Http\Requests\Project\Members;

use App\Models\ProjectMember;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('projectMember')) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectMember = $this->route('projectMember');

        assert($projectMember instanceof ProjectMember);

        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('project_members', 'user_id')
                    ->where('project_id', $projectMember->project_id)
                    ->ignore($projectMember->id),
            ],
            'project_role_id' => [
                'required',
                'integer',
                Rule::exists('project_roles', 'id')
                    ->where('project_id', $projectMember->project_id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}
