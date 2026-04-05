<?php

namespace App\Enums;

enum VisitFrequency: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case BiWeekly = 'bi_weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
            self::BiWeekly => 'Bi-Weekly',
            self::Monthly => 'Monthly',
        };
    }
}
