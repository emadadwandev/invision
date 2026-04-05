<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\CampaignType;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'type',
        'status',
        'start_date',
        'end_date',
        'budget',
        'spent',
        'offer_details',
        'reward_details',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => CampaignType::class,
            'status' => CampaignStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
            'spent' => 'decimal:2',
            'offer_details' => 'array',
            'reward_details' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'campaign_stores')->withTimestamps();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products')->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(CampaignTask::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CampaignEntry::class);
    }

    public function budgetUtilization(): float
    {
        if (! $this->budget || $this->budget == 0) {
            return 0;
        }

        return round(($this->spent / $this->budget) * 100, 1);
    }
}
