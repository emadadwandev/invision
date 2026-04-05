<?php

namespace App\Enums;

enum ObservationType: string
{
    case Sales = 'sales';
    case Posm = 'posm';
    case Pricing = 'pricing';
    case Display = 'display';
    case Promotion = 'promotion';
    case StockLevel = 'stock_level';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Sales => 'Sales',
            self::Posm => 'POSM',
            self::Pricing => 'Pricing',
            self::Display => 'Display',
            self::Promotion => 'Promotion',
            self::StockLevel => 'Stock Level',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Sales => 'blue',
            self::Posm => 'purple',
            self::Pricing => 'green',
            self::Display => 'yellow',
            self::Promotion => 'indigo',
            self::StockLevel => 'orange',
            self::Other => 'gray',
        };
    }
}
