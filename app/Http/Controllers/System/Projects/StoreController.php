<?php

declare(strict_types = 1);

namespace App\Http\Controllers\System\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\Projects\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreProjectRequest $request): RedirectResponse
    {
        Project::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Projeto cadastrado.']);

        return to_route('system.projects.index');
    }
}
