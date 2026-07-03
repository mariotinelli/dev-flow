<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Projects;

enum MemberPermissions: string
{
    case View   = 'project.members.view';
    case Create = 'project.members.create';
    case Update = 'project.members.update';
    case Delete = 'project.members.delete';

    public function label(): string
    {
        return match ($this) {
            self::View   => 'Visualizar membros',
            self::Create => 'Cadastrar membros',
            self::Update => 'Editar membros',
            self::Delete => 'Remover membros',
        };
    }

    public function group(): string
    {
        return 'Membros';
    }
}
