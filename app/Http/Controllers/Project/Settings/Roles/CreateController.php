<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Project\Settings\Roles;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\ProjectRole;
use Inertia\Inertia;
use Inertia\Response;

class CreateController extends Controller
{
    public function __invoke(): Response
    {
        $this->authorize('create', ProjectRole::class);

        return Inertia::render('project/settings/roles/Create', [
            'permissionGroups' => Permission::projectGroupedOptions(),
        ]);
    }
}
