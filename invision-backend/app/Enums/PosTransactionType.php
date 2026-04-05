<?php

namespace App\Enums;

enum PosTransactionType: string
{
    case SellOut = 'sell_out';
    case SellThrough = 'sell_through';
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::SellOut => 'Sell Out',
            self::SellThrough => 'Sell Through',
            self::Return => 'Return',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SellOut => 'blue',
            self::SellThrough => 'green',
            self::Return => 'red',
        };
    }
}
