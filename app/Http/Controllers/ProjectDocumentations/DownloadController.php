<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectDocumentations;

use App\Http\Controllers\Controller;
use App\Models\ProjectDocumentation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function __invoke(ProjectDocumentation $projectDocumentation): RedirectResponse
    {
        $this->authorize('view', $projectDocumentation);

        abort_unless($projectDocumentation->file_path, 404);

        return redirect()->away(
            Storage::disk('s3')->temporaryUrl($projectDocumentation->file_path, now()->addMinutes(5)),
        );
    }
}
