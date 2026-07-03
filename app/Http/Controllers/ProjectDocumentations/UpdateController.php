<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectDocumentations;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectDocumentations\UpdateProjectDocumentationRequest;
use App\Models\ProjectDocumentation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UpdateController extends Controller
{
    public function __invoke(UpdateProjectDocumentationRequest $request, ProjectDocumentation $projectDocumentation): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['file']) && $validated['file'] instanceof UploadedFile) {
            $file = $validated['file'];
            unset($validated['file']);

            if ($projectDocumentation->file_path) {
                Storage::disk('s3')->delete($projectDocumentation->file_path);
            }

            $validated['file_path'] = $file->store('project-documentations', 's3');
        }

        $projectDocumentation->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Documentação atualizada.']);

        return to_route('project.documentations.index');
    }
}
