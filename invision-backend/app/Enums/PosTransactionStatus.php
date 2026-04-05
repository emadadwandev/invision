<?php

namespace App\Enums;

enum PosTransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Voided = 'voided';
    case Synced = 'synced';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
            self::Voided => 'Voided',
            self::Synced => 'Synced',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Completed => 'green',
            self::Voided => 'red',
            self::Synced => 'blue',
        };
    }
}
