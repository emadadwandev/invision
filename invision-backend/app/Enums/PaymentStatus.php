<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
    case Overdue = 'overdue';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::PartiallyPaid => 'Partially Paid',
            self::Overdue => 'Overdue',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Paid => 'green',
            self::PartiallyPaid => 'blue',
            self::Overdue => 'red',
            self::Refunded => 'gray',
        };
    }
}
