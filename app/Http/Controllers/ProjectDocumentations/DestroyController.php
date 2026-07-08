<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectDocumentations;

use App\Http\Controllers\Controller;
use App\Models\ProjectDocumentation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DestroyController extends Controller
{
    public function __invoke(ProjectDocumentation $projectDocumentation): RedirectResponse
    {
        $this->authorize('delete', $projectDocumentation);

        if ($projectDocumentation->file_path) {
            Storage::disk('s3')->delete($projectDocumentation->file_path);
        }

        $projectDocumentation->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Documentação removida.']);

        return to_route('project.documentations.index');
    }
}
