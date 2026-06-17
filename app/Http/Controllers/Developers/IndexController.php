<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Developers;

use App\Enums\BaseStatus;
use App\Enums\ContractType;
use App\Enums\Seniority;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeveloperResource;
use App\Models\Developer;
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
        $filters = $request->validate([
            'search'        => ['nullable', 'string', 'max:255'],
            'contract_type' => ['nullable', 'integer', Rule::in(ContractType::values())],
            'seniority'     => ['nullable', 'integer', Rule::in(Seniority::values())],
            'status'        => ['nullable', 'string', Rule::in(BaseStatus::values())],
        ]);

        $developers = Developer::query()
            ->withTrashed()
            ->with(['user' => fn ($query) => $query->withTrashed()])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('role', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search): void {
                            $query
                                ->withTrashed()
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['contract_type'] ?? null, fn ($query, mixed $contractType) => $query->where('contract_type', (int) $contractType))
            ->when($filters['seniority'] ?? null, fn ($query, mixed $seniority) => $query->where('seniority', (int) $seniority))
            ->when($filters['status'] ?? null, function ($query, string $status): void {
                match ($status) {
                    BaseStatus::Active->value   => $query->whereNull('deleted_at'),
                    BaseStatus::Inactive->value => $query->whereNotNull('deleted_at'),
                };
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('developers/Index', [
            'developers' => DeveloperResource::collection($developers),
            'filters'    => [
                'search'        => $filters['search'] ?? '',
                'contract_type' => isset($filters['contract_type']) ? (string) $filters['contract_type'] : 'all',
                'seniority'     => isset($filters['seniority']) ? (string) $filters['seniority'] : 'all',
                'status'        => $filters['status'] ?? 'all',
            ],
            'contractTypes' => ContractType::options(),
            'seniorities'   => Seniority::options(),
            'statuses'      => BaseStatus::options(),
        ]);
    }
}
