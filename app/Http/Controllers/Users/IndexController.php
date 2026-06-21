<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Users;

use App\Enums\BaseStatus;
use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->validate([
            'search'        => ['nullable', 'string', 'max:255'],
            'job_title'     => ['nullable', 'integer', Rule::in(JobTitle::values())],
            'contract_type' => ['nullable', 'integer', Rule::in(ContractType::values())],
            'seniority'     => ['nullable', 'integer', Rule::in(Seniority::values())],
            'status'        => ['nullable', 'string', Rule::in(BaseStatus::values())],
        ]);

        $users = User::query()
            ->withoutRole('admin')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->whereAny(['role', 'name', 'email'], 'like', "%{$search}%");
            })
            ->when($filters['job_title'] ?? null, fn ($query, mixed $jobTitle) => $query->where('job_title', (int) $jobTitle))
            ->when($filters['contract_type'] ?? null, fn ($query, mixed $contractType) => $query->where('contract_type', (int) $contractType))
            ->when($filters['seniority'] ?? null, fn ($query, mixed $seniority) => $query->where('seniority', (int) $seniority))
            ->when($filters['status'] ?? null, function ($query, string $status): void {
                match ($status) {
                    BaseStatus::Active->value   => $query->whereNull('deleted_at'),
                    BaseStatus::Inactive->value => $query->whereNotNull('deleted_at'),
                    default                     => null,
                };
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('users/Index', [
            'users'      => UserResource::collection($users),
            'can'        => [
                'create' => $request->user()->can('create', User::class),
            ],
            'filters' => [
                'search'        => $filters['search'] ?? '',
                'job_title'     => isset($filters['job_title']) ? (string) $filters['job_title'] : 'all',
                'contract_type' => isset($filters['contract_type']) ? (string) $filters['contract_type'] : 'all',
                'seniority'     => isset($filters['seniority']) ? (string) $filters['seniority'] : 'all',
                'status'        => $filters['status'] ?? 'all',
            ],
            'jobTitles'     => JobTitle::options(),
            'contractTypes' => ContractType::options(),
            'seniorities'   => Seniority::options(),
            'statuses'      => BaseStatus::options(),
        ]);
    }
}
