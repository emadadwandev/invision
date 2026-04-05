<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditAccount extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'credit_limit',
        'current_balance',
        'last_payment_at',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'last_payment_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function availableCredit(): float
    {
        return (float) $this->credit_limit - (float) $this->current_balance;
    }
}
