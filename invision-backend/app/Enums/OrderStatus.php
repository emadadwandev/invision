<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Confirmed => 'Confirmed',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Returned => 'Returned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Confirmed => 'blue',
            self::Delivered => 'green',
            self::Cancelled => 'red',
            self::Returned => 'yellow',
        };
    }
}
