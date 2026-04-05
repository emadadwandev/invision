<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'priority',
        'title',
        'body',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'priority' => NotificationPriority::class,
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
