<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectDocumentations;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectDocumentations\StoreProjectDocumentationRequest;
use App\Models\ProjectDocumentation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function __invoke(StoreProjectDocumentationRequest $request): RedirectResponse
    {
        ProjectDocumentation::query()->create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Documentação cadastrada.']);

        return to_route('project.documentations.index');
    }
}
