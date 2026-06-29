<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Projects;

enum MemberPermissions: string
{
    case View   = 'project.members.view';
    case Manage = 'project.members.manage';

    public function label(): string
    {
        return match ($this) {
            self::View   => 'Visualizar membros',
            self::Manage => 'Gerenciar membros',
        };
    }

    public function group(): string
    {
        return 'Membros';
    }
}
