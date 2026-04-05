<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignEntry extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'campaign_task_id',
        'store_id',
        'user_id',
        'entry_type',
        'code',
        'quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(CampaignTask::class, 'campaign_task_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
