<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Developers;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ActivateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Developer $developer): RedirectResponse
    {
        $this->authorize('restore', $developer);

        DB::transaction(function () use ($developer): void {
            $developer->load('user');

            $developer->restore();
            $developer->user->restore();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Desenvolvedor ativado.']);

        return to_route('developers.index');
    }
}
