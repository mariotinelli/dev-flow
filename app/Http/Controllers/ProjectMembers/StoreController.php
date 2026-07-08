<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectMembers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectMembers\StoreProjectMemberRequest;
use App\Models\ProjectMember;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function __invoke(StoreProjectMemberRequest $request): RedirectResponse
    {
        ProjectMember::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Membro do projeto cadastrado.']);

        return to_route('project.members.index');
    }
}
