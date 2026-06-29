<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Projects;

enum DocumentPermissions: string
{
    case View   = 'project.documents.view';
    case Manage = 'project.documents.manage';

    public function label(): string
    {
        return match ($this) {
            self::View   => 'Visualizar documentos',
            self::Manage => 'Gerenciar documentos',
        };
    }

    public function group(): string
    {
        return 'Documentação';
    }
}
