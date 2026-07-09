<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Intelligence;

enum AiChatPermissions: string
{
    case Use = 'intelligence.ai-chat.use';

    public function label(): string
    {
        return match ($this) {
            self::Use => 'Utilizar Chat IA',
        };
    }

    public function group(): string
    {
        return 'Chat IA';
    }
}
