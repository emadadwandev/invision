<?php

namespace App\Enums;

enum TaskAssignmentStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::InProgress => 'blue',
            self::Completed => 'green',
            self::Verified => 'indigo',
            self::Rejected => 'red',
        };
    }
}
