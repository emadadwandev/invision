<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesAreaAssignment extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'sales_area_id',
        'user_id',
        'role',
        'product_lines',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'product_lines' => 'array',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function salesArea(): BelongsTo
    {
        return $this->belongsTo(SalesArea::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
