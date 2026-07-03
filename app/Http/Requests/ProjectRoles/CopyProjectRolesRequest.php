<?php

declare(strict_types = 1);

namespace App\Http\Requests\ProjectRoles;

use App\Models\ProjectRole;
use App\Support\CurrentProject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CopyProjectRolesRequest extends FormRequest
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

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProjectRole::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source_project_id' => ['required', 'integer', Rule::in($this->copyableProjectIds())],
        ];
    }

    /**
     * @return list<int>
     */
    private function copyableProjectIds(): array
    {
        $user = $this->user();

        if (!$user) {
            return [];
        }

        $currentProject = $this->currentProject->resolve();

        if (!$currentProject) {
            return [];
        }

        return $this->currentProject->availableFor($user)
            ->loadCount('projectRoles')
            ->reject(fn ($project): bool => $project->id === $currentProject->id)
            ->filter(fn ($project): bool => $project->project_roles_count > 0)
            ->pluck('id')
            ->values()
            ->all();
    }
}
