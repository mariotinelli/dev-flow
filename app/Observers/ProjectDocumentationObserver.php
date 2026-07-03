<?php

declare(strict_types = 1);

namespace App\Observers;

use App\Enums\ProjectDocumentationType;
use App\Models\ProjectDocumentation;
use Illuminate\Support\Facades\Storage;

final class ProjectDocumentationObserver
{
    public function updating(ProjectDocumentation $projectDocumentation): void
    {
        if (in_array($projectDocumentation->type, [ProjectDocumentationType::File, ProjectDocumentationType::Image], true)) {
            $projectDocumentation->url = null;

            return;
        }

        if ($projectDocumentation->type === ProjectDocumentationType::Link) {
            if ($projectDocumentation->file_path) {
                Storage::disk('s3')->delete($projectDocumentation->file_path);
            }

            $projectDocumentation->file_path = null;
        }
    }
}
