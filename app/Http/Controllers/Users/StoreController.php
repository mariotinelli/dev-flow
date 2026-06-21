<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make(Str::password(32)),
            'avatar_path'   => $request->file('avatar')?->store('users/avatars', 'public'),
            'job_title'     => $validated['job_title'],
            'contract_type' => $validated['contract_type'],
            'seniority'     => $validated['seniority'],
        ]);

        Password::sendResetLink(['email' => $user->email]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuário cadastrado.']);

        return to_route('users.index');
    }
}
