<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Project;

enum TaskPermissions: string
{
    case View   = 'project.tasks.view';
    case Create = 'project.tasks.create';
    case Update = 'project.tasks.update';
    case Delete = 'project.tasks.delete';

    public function label(): string
    {
        return match ($this) {
            self::View   => 'Visualizar tarefas',
            self::Create => 'Criar tarefas',
            self::Update => 'Editar tarefas',
            self::Delete => 'Excluir tarefas',
        };
    }

    public function group(): string
    {
        return 'Tarefas';
    }
}
