<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Projects;

enum DocumentPermissions: string
{
    case View   = 'project.documents.view';
    case Create = 'project.documents.create';
    case Update = 'project.documents.update';
    case Delete = 'project.documents.delete';

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
