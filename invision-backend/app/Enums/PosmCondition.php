<?php

namespace App\Enums;

enum PosmCondition: string
{
    case Good = 'good';
    case Damaged = 'damaged';
    case Missing = 'missing';
    case NeedsReplacement = 'needs_replacement';

    public function label(): string
    {
        return match ($this) {
            self::Good => 'Good',
            self::Damaged => 'Damaged',
            self::Missing => 'Missing',
            self::NeedsReplacement => 'Needs Replacement',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Good => 'green',
            self::Damaged => 'yellow',
            self::Missing => 'red',
            self::NeedsReplacement => 'orange',
        };
    }
}
