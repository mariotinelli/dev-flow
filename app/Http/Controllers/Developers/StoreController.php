<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Developers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeveloperRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreDeveloperRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($request, $validated): User {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make(Str::password(32)),
            ]);

            $user->developer()->create([
                'avatar_path'   => $request->file('avatar')?->store('developers/avatars', 'public'),
                'role'          => $validated['role'],
                'contract_type' => $validated['contract_type'],
                'seniority'     => $validated['seniority'],
            ]);

            return $user;
        });

        Password::sendResetLink(['email' => $user->email]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Desenvolvedor cadastrado.']);

        return to_route('developers.index');
    }
}
