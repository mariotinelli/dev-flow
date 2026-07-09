<?php

declare(strict_types = 1);

namespace App\Http\Requests\Project\Members;

use App\Models\ProjectMember;
use App\Support\CurrentProject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectMemberRequest extends FormRequest
{
    public function __construct(
        private CurrentProject $currentProject,
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null,
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('create', ProjectMember::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->currentProject->resolve();

        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('project_members', 'user_id')->where('project_id', $project?->id),
            ],
            'project_role_id' => [
                'required',
                'integer',
                Rule::exists('project_roles', 'id')
                    ->where('project_id', $project?->id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}
