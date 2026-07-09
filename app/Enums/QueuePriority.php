<?php

declare(strict_types = 1);

namespace App\Enums;

enum QueuePriority: string
{
    case LowPriority  = 'low-priority';
    case HighPriority = 'high-priority';
    case LongTimeout  = 'long-timeout';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
