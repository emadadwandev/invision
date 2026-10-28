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

    /** Length of the rotating cycle: weekly repeats every week, monthly rotates 4 different weeks. */
    public function cycleWeeks(): int
    {
        return match ($this) {
            self::Daily, self::Weekly => 1,
            self::BiWeekly => 2,
            self::Monthly => 4,
        };
    }
}
