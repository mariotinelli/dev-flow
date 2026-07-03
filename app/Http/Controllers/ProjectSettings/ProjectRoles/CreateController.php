<?php

declare(strict_types = 1);

namespace App\Http\Controllers\ProjectSettings\ProjectRoles;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\ProjectRole;
use App\Support\CurrentProject;
use Inertia\Inertia;
use Inertia\Response;

class CreateController extends Controller
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    public function __invoke(): Response
    {
        $this->authorize('create', ProjectRole::class);

        abort_unless($this->currentProject->resolve(), 404);

        return Inertia::render('project-settings/roles/Create', [
            'permissionGroups' => Permission::projectGroupedOptions(),
        ]);
    }
}
