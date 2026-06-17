<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class DestroyController extends Controller
{
    public function __invoke(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if ($role->users()->exists()) {
            Inertia::flash('toast', [
                'type'    => 'error',
                'message' => 'Este perfil está em uso e não pode ser excluído.',
            ]);

            return to_route('roles.index');
        }

        $role->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Perfil excluído.']);

        return to_route('roles.index');
    }
}
