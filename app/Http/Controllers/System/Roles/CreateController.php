<?php

declare(strict_types = 1);

namespace App\Http\Controllers\System\Roles;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class CreateController extends Controller
{
    public function __invoke(): Response
    {
        $this->authorize('create', Role::class);

        return Inertia::render('system/roles/Create', [
            'permissionGroups' => Permission::systemGroupedOptions(),
        ]);
    }
}
