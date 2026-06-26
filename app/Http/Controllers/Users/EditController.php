<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Users;

use App\Actions\GetSelectOptions;
use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class EditController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('users/Edit', [
            'user'          => (new UserResource($user))->resolve($request),
            'jobTitles'     => JobTitle::options(),
            'contractTypes' => ContractType::options(),
            'seniorities'   => Seniority::options(),
            'roles'         => app(GetSelectOptions::class)->handle(Role::query()->whereNot('name', 'admin'), 'id', 'name'),
        ]);
    }
}
