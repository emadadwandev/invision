<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'sales_order_id',
        'user_id',
        'payment_method',
        'amount',
        'reference_number',
        'check_number',
        'check_date',
        'bank_name',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'check_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function depositReceipt(): HasOne
    {
        return $this->hasOne(DepositReceipt::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }
}
