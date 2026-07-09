<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * @var array<string, string>
     */
    private array $renames = [
        'projects.view'            => 'system.projects.view',
        'projects.create'          => 'system.projects.create',
        'projects.update'          => 'system.projects.update',
        'projects.delete'          => 'system.projects.delete',
        'projects.restore'         => 'system.projects.restore',
        'users.view'               => 'system.users.view',
        'users.create'             => 'system.users.create',
        'users.update'             => 'system.users.update',
        'users.delete'             => 'system.users.delete',
        'users.restore'            => 'system.users.restore',
        'roles.view'               => 'system.roles.view',
        'roles.create'             => 'system.roles.create',
        'roles.update'             => 'system.roles.update',
        'roles.delete'             => 'system.roles.delete',
        'project.ai-chat.use'      => 'intelligence.ai-chat.use',
        'project.documents.view'   => 'project.documentations.view',
        'project.documents.create' => 'project.documentations.create',
        'project.documents.update' => 'project.documentations.update',
        'project.documents.delete' => 'project.documentations.delete',
    ];

    /**
     * @var list<string>
     */
    private array $settingsPermissions = [
        'project.settings.roles.view',
        'project.settings.roles.create',
        'project.settings.roles.update',
        'project.settings.roles.delete',
        'project.settings.roles.restore',
        'project.settings.roles.copy',
        'project.settings.gitlab.view',
        'project.settings.loom.view',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            foreach ($this->renames as $oldName => $newName) {
                $this->renamePermission($oldName, $newName);
            }

            $this->expandPermission('project.settings.manage', $this->settingsPermissions);
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $this->collapsePermissions($this->settingsPermissions, 'project.settings.manage');

            foreach (array_reverse($this->renames, true) as $oldName => $newName) {
                $this->renamePermission($newName, $oldName);
            }
        });
    }

    private function renamePermission(string $oldName, string $newName): void
    {
        $oldPermission = DB::table('permissions')->where('name', $oldName)->first();

        if (!$oldPermission) {
            return;
        }

        $newPermission = $this->permission((string) $newName, (string) $oldPermission->guard_name);

        $this->copyPermissionLinks((int) $oldPermission->id, (int) $newPermission->id);
        $this->deletePermissionLinks((int) $oldPermission->id);

        DB::table('permissions')->where('id', $oldPermission->id)->delete();
    }

    /**
     * @param  list<string>  $newNames
     */
    private function expandPermission(string $oldName, array $newNames): void
    {
        $oldPermission = DB::table('permissions')->where('name', $oldName)->first();

        if (!$oldPermission) {
            foreach ($newNames as $newName) {
                $this->permission($newName);
            }

            return;
        }

        foreach ($newNames as $newName) {
            $newPermission = $this->permission($newName, (string) $oldPermission->guard_name);

            $this->copyPermissionLinks((int) $oldPermission->id, (int) $newPermission->id);
        }

        $this->deletePermissionLinks((int) $oldPermission->id);

        DB::table('permissions')->where('id', $oldPermission->id)->delete();
    }

    /**
     * @param  list<string>  $oldNames
     */
    private function collapsePermissions(array $oldNames, string $newName): void
    {
        $oldPermissions = DB::table('permissions')->whereIn('name', $oldNames)->get();

        if ($oldPermissions->isEmpty()) {
            return;
        }

        $newPermission = $this->permission($newName, (string) $oldPermissions->first()->guard_name);

        foreach ($oldPermissions as $oldPermission) {
            $this->copyPermissionLinks((int) $oldPermission->id, (int) $newPermission->id);
            $this->deletePermissionLinks((int) $oldPermission->id);
        }

        DB::table('permissions')->whereIn('name', $oldNames)->delete();
    }

    private function permission(string $name, string $guardName = 'web'): object
    {
        $permission = DB::table('permissions')->where('name', $name)->first();

        if ($permission) {
            return $permission;
        }

        $now = now();

        DB::table('permissions')->insert([
            'name'       => $name,
            'guard_name' => $guardName,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return DB::table('permissions')->where('name', $name)->first();
    }

    private function copyPermissionLinks(int $fromPermissionId, int $toPermissionId): void
    {
        if ($fromPermissionId === $toPermissionId) {
            return;
        }

        foreach (DB::table('role_has_permissions')->where('permission_id', $fromPermissionId)->get() as $link) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $toPermissionId,
                'role_id'       => $link->role_id,
            ]);
        }

        foreach (DB::table('model_has_permissions')->where('permission_id', $fromPermissionId)->get() as $link) {
            DB::table('model_has_permissions')->updateOrInsert([
                'permission_id' => $toPermissionId,
                'model_type'    => $link->model_type,
                'model_id'      => $link->model_id,
            ]);
        }

        foreach (DB::table('project_role_permissions')->where('permission_id', $fromPermissionId)->get() as $link) {
            DB::table('project_role_permissions')->updateOrInsert([
                'project_role_id' => $link->project_role_id,
                'permission_id'   => $toPermissionId,
            ]);
        }
    }

    private function deletePermissionLinks(int $permissionId): void
    {
        DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
        DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
        DB::table('project_role_permissions')->where('permission_id', $permissionId)->delete();
    }
};
