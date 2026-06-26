<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Users;

use App\Actions\GetSelectOptions;
use App\Enums\BaseStatus;
use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\IndexUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(IndexUserRequest $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->validated();

        $users = User::query()
            ->withTrashed()
            ->withoutAdmin()
            ->filters($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('users/Index', [
            'users' => UserResource::collection($users),
            'can'   => [
                'create' => $request->user()->can('create', User::class),
            ],
            'filters' => [
                'search'        => $filters['search'] ?? '',
                'role'          => isset($filters['role']) ? (string) $filters['role'] : 'all',
                'job_title'     => isset($filters['job_title']) ? (string) $filters['job_title'] : 'all',
                'contract_type' => isset($filters['contract_type']) ? (string) $filters['contract_type'] : 'all',
                'seniority'     => isset($filters['seniority']) ? (string) $filters['seniority'] : 'all',
                'status'        => $filters['status'] ?? 'all',
            ],
            'jobTitles'     => JobTitle::options(),
            'contractTypes' => ContractType::options(),
            'seniorities'   => Seniority::options(),
            'statuses'      => BaseStatus::options(),
            'roles'         => app(GetSelectOptions::class)->handle(Role::query()->whereNot('name', 'admin'), 'id', 'name'),
        ]);
    }
}
