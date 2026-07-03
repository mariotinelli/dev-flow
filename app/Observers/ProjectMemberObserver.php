<?php

declare(strict_types = 1);

namespace App\Observers;

use App\Models\ProjectMember;
use App\Support\CurrentProject;

final class ProjectMemberObserver
{
    public function __construct(
        private CurrentProject $currentProject,
    ) {
    }

    public function created(ProjectMember $projectMember): void
    {
        $this->currentProject->clearCache();
    }

    public function updated(ProjectMember $projectMember): void
    {
        $this->currentProject->clearCache();
    }

    public function deleted(ProjectMember $projectMember): void
    {
        $this->currentProject->clearCache();
    }
}
