<?php

namespace App\Enums;

enum VisitStatus: string
{
    case Pending = 'pending';
    case CheckedIn = 'checked_in';
    case Completed = 'completed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::CheckedIn => 'Checked In',
            self::Completed => 'Completed',
            self::Skipped => 'Skipped',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::CheckedIn => 'blue',
            self::Completed => 'green',
            self::Skipped => 'red',
        };
    }
}
