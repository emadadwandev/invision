<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignTaskPhoto extends Model
{
    protected $fillable = [
        'campaign_task_id',
        'photo_path',
        'caption',
        'type',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(CampaignTask::class, 'campaign_task_id');
    }
}
