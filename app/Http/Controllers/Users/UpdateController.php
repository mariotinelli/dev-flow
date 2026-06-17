<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UpdateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated, $user): void {
            $avatarPath = $user->avatar_path;

            if ($request->hasFile('avatar')) {
                if ($avatarPath !== null) {
                    Storage::disk('public')->delete($avatarPath);
                }

                $avatarPath = $request->file('avatar')->store('users/avatars', 'public');
            }

            $user->update([
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'avatar_path'   => $avatarPath,
                'job_title'     => $validated['job_title'],
                'contract_type' => $validated['contract_type'],
                'seniority'     => $validated['seniority'],
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuário atualizado.']);

        return to_route('users.index');
    }
}
