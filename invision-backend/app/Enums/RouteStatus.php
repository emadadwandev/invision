<?php

namespace App\Enums;

enum RouteStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'blue',
            self::InProgress => 'yellow',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }
}
