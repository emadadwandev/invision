<?php

namespace App\Enums;

enum StockMovementType: string
{
    case StockIn = 'stock_in';
    case StockOut = 'stock_out';
    case Adjustment = 'adjustment';
    case Return = 'return';
    case SellOut = 'sell_out';
    case SellThrough = 'sell_through';

    public function label(): string
    {
        return match ($this) {
            self::StockIn => 'Stock In',
            self::StockOut => 'Stock Out',
            self::Adjustment => 'Adjustment',
            self::Return => 'Return',
            self::SellOut => 'Sell Out',
            self::SellThrough => 'Sell Through',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::StockIn => 'green',
            self::StockOut => 'red',
            self::Adjustment => 'yellow',
            self::Return => 'blue',
            self::SellOut => 'indigo',
            self::SellThrough => 'purple',
        };
    }
}
