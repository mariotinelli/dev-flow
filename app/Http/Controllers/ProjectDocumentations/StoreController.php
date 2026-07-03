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
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('project-documentations', 's3');
            unset($data['file']);
        }

        ProjectDocumentation::query()->create([
            ...$data,
            'author_id' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Documentação cadastrada.']);

        return to_route('project.documentations.index');
    }
}
