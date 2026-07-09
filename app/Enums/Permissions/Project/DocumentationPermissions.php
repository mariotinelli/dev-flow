<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Project;

enum DocumentationPermissions: string
{
    case View   = 'project.documentations.view';
    case Create = 'project.documentations.create';
    case Update = 'project.documentations.update';
    case Delete = 'project.documentations.delete';

    public function label(): string
    {
        return match ($this) {
            self::View   => 'Visualizar documentos',
            self::Create => 'Cadastrar documentos',
            self::Update => 'Editar documentos',
            self::Delete => 'Excluir documentos',
        };
    }

    public function group(): string
    {
        return 'Documentação';
    }
}
