<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectDocumentations;

use App\Http\Controllers\Controller;
use App\Models\ProjectDocumentation;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __invoke(ProjectDocumentation $projectDocumentation): StreamedResponse
    {
        $this->authorize('view', $projectDocumentation);

        abort_unless($projectDocumentation->file_path, 404);

        $filename = $projectDocumentation->file_original_name ?? basename($projectDocumentation->file_path);

        return Storage::disk('s3')->download($projectDocumentation->file_path, $filename);
    }
}
