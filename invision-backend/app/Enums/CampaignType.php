<?php

namespace App\Enums;

enum CampaignType: string
{
    case Promotion = 'promotion';
    case Discount = 'discount';
    case Sampling = 'sampling';
    case Display = 'display';
    case Posm = 'posm';
    case BuyGetFree = 'buy_get_free';

    public function label(): string
    {
        return match ($this) {
            self::Promotion => 'Promotion',
            self::Discount => 'Discount',
            self::Sampling => 'Sampling',
            self::Display => 'Display',
            self::Posm => 'POSM',
            self::BuyGetFree => 'Buy & Get Free',
        };
    }
}
