<?php

namespace App\Enums;

enum ContactType: string
{
    case Marketing = 'marketing';
    case ShopManager = 'shop_manager';
    case Sales = 'sales';
    case Management = 'management';

    public function label(): string
    {
        return match ($this) {
            self::Marketing => 'Marketing',
            self::ShopManager => 'Shop Manager',
            self::Sales => 'Sales',
            self::Management => 'Management',
        };
    }
}
