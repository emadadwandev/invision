<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignTask extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'store_id',
        'assigned_to',
        'status',
        'instructions',
        'notes',
        'completed_at',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'completed_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(CampaignTaskPhoto::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CampaignEntry::class);
    }
}
