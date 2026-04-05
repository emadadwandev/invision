<?php

namespace App\Models;

use App\Enums\PosTransactionStatus;
use App\Enums\PosTransactionType;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosTransaction extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'pos_terminal_id',
        'user_id',
        'transaction_number',
        'type',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'payment_method',
        'notes',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => PosTransactionType::class,
            'status' => PosTransactionStatus::class,
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'synced_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosTransactionItem::class);
    }
}
