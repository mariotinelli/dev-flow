<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectDocumentations;

use App\Enums\ProjectDocumentationType;
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

        $type = (int) ($validated['type'] ?? $projectDocumentation->type->value);

        $previousFilePath = $projectDocumentation->file_path;
        $previousType     = $projectDocumentation->type->value;

        $typeChanged = $type !== $previousType;

        if ($previousFilePath && ($validated['remove_file'] ?? false)) {
            Storage::disk('s3')->delete($previousFilePath);

            $validated['file_path']          = null;
            $validated['file_original_name'] = null;
        }

        if (in_array($type, [ProjectDocumentationType::File->value, ProjectDocumentationType::Image->value], true) && isset($validated['file']) && $validated['file'] instanceof UploadedFile) {
            $file = $validated['file'];

            $validated['file_path']          = $file->store('project-documentations', 's3');
            $validated['file_original_name'] = $file->getClientOriginalName();

            if ($previousFilePath && $previousFilePath !== $validated['file_path']) {
                Storage::disk('s3')->delete($previousFilePath);
            }
        }

        unset($validated['file'], $validated['remove_file']);

        if ($typeChanged) {
            if ($type === ProjectDocumentationType::Link->value) {
                $validated['file_path']          = null;
                $validated['file_original_name'] = null;

                if ($previousFilePath) {
                    Storage::disk('s3')->delete($previousFilePath);
                }
            }

            if (in_array($type, [ProjectDocumentationType::File->value, ProjectDocumentationType::Image->value], true)) {
                $validated['url'] = null;
            }
        }

        $projectDocumentation->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Documentação atualizada.']);

        return to_route('project.documentations.index');
    }
}
