<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Developers;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DestroyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Developer $developer): RedirectResponse
    {
        DB::transaction(function () use ($developer): void {
            $developer->load('user');

            $developer->delete();
            $developer->user->delete();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Developer removed.')]);

        return to_route('developers.index');
    }
}
