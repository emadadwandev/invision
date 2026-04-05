<?php

namespace App\Enums;

enum StoreCategory: string
{
    case Supermarket = 'supermarket';
    case Hypermarket = 'hypermarket';
    case MiniMarket = 'mini_market';
    case Pharmacy = 'pharmacy';
    case ConvenienceStore = 'convenience_store';
    case WholesaleStore = 'wholesale_store';
    case Kiosk = 'kiosk';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Supermarket => 'Supermarket',
            self::Hypermarket => 'Hypermarket',
            self::MiniMarket => 'Mini Market',
            self::Pharmacy => 'Pharmacy',
            self::ConvenienceStore => 'Convenience Store',
            self::WholesaleStore => 'Wholesale Store',
            self::Kiosk => 'Kiosk',
            self::Other => 'Other',
        };
    }
}
