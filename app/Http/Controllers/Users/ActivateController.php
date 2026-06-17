<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ActivateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user): RedirectResponse
    {
        $this->authorize('restore', $user);

        $user->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuário ativado.']);

        return to_route('users.index');
    }
}
