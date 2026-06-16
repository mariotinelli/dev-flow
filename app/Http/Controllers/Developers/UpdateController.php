<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Developers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDeveloperRequest;
use App\Models\Developer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UpdateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateDeveloperRequest $request, Developer $developer): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated, $developer): void {
            $developer->load('user');

            $developer->user->update([
                'name'  => $validated['name'],
                'email' => $validated['email'],
            ]);

            $avatarPath = $developer->avatar_path;

            if ($request->hasFile('avatar')) {
                if ($avatarPath !== null) {
                    Storage::disk('public')->delete($avatarPath);
                }

                $avatarPath = $request->file('avatar')->store('developers/avatars', 'public');
            }

            $developer->update([
                'avatar_path'   => $avatarPath,
                'role'          => $validated['role'],
                'contract_type' => $validated['contract_type'],
                'seniority'     => $validated['seniority'],
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Developer updated.')]);

        return to_route('developers.index');
    }
}
