<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarWeek extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'year',
        'week_number',
        'start_date',
        'end_date',
        'label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
