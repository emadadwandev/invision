<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenant;

class SyncQueueItem extends Model
{
    use HasTenant;

    protected $table = 'sync_queue';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'device_id',
        'client_id',
        'entity_type',
        'action',
        'payload',
        'status',
        'error_message',
        'server_response',
        'client_timestamp',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'server_response' => 'array',
            'client_timestamp' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isConflict(): bool
    {
        return $this->status === 'conflict';
    }
}
