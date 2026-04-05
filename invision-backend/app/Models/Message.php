<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'sender_id',
        'subject',
        'body',
        'is_group',
    ];

    protected function casts(): array
    {
        return [
            'is_group' => 'boolean',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class);
    }
}
