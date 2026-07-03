<?php

declare(strict_types = 1);

namespace App\Observers;

use App\Models\ProjectMember;
use App\Support\CurrentProject;

final class ProjectMemberObserver
{
    public function created(ProjectMember $projectMember): void
    {
        CurrentProject::clearCache();
    }

    public function updated(ProjectMember $projectMember): void
    {
        CurrentProject::clearCache();
    }

    public function deleted(ProjectMember $projectMember): void
    {
        CurrentProject::clearCache();
    }
}
