<?php

namespace App\Models;

use App\Enums\RouteStatus;
use App\Enums\VisitFrequency;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoutePlan extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'assigned_to',
        'frequency',
        'start_date',
        'end_date',
        'status',
        'total_stores',
    ];

    protected function casts(): array
    {
        return [
            'frequency' => VisitFrequency::class,
            'status' => RouteStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'total_stores' => 'integer',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function routeStores(): HasMany
    {
        return $this->hasMany(RoutePlanStore::class)->orderBy('visit_order');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(RouteInstance::class);
    }

    public function recalculateTotalStores(): void
    {
        $this->update(['total_stores' => $this->routeStores()->count()]);
    }
}
