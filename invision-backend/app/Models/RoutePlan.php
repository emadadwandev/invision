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
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RoutePlan extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'assigned_to',
        'frequency',
        'work_days',
        'start_date',
        'end_date',
        'status',
        'total_stores',
    ];

    protected function casts(): array
    {
        return [
            'frequency' => VisitFrequency::class,
            'work_days' => 'array',
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
        $this->update(['total_stores' => $this->routeStores()->distinct()->count('store_id')]);
    }

    /** Week index (1-based) of $date inside the plan's rotating cycle. */
    public function cycleWeek(Carbon $date): int
    {
        $weeks = intdiv((int) $this->start_date->copy()->startOfWeek()->diffInDays($date->copy()->startOfWeek()), 7);

        return ($weeks % $this->frequency->cycleWeeks()) + 1;
    }

    /**
     * Stores to visit on $date. Rows with week_start matching the date's week
     * replace the regular template for that week; otherwise template rows apply,
     * filtered by weekday (null = every work day) and cycle week (null = every week).
     */
    public function storesForDate(Carbon|string $date): Collection
    {
        $date = Carbon::parse($date)->startOfDay();

        if ($date->lt($this->start_date) || ($this->end_date && $date->gt($this->end_date))) {
            return collect();
        }

        $weekStart = $date->copy()->startOfWeek()->toDateString();
        $rows = $this->routeStores;
        $override = $rows->filter(fn (RoutePlanStore $r) => $r->week_start?->toDateString() === $weekStart);
        $pool = $override->isNotEmpty() ? $override : $rows->whereNull('week_start');

        $dow = $date->dayOfWeekIso;
        $isWorkDay = in_array($dow, $this->work_days ?: range(1, 7));
        $cycleWeek = $this->cycleWeek($date);
        $usesCycle = $override->isEmpty() && $this->frequency->cycleWeeks() > 1;

        return $pool
            ->filter(fn (RoutePlanStore $r) => $r->day_of_week ? $r->day_of_week === $dow : $isWorkDay)
            ->filter(fn (RoutePlanStore $r) => ! $usesCycle || ! $r->week_number || $r->week_number === $cycleWeek)
            ->sortBy('visit_order')
            ->unique('store_id')
            ->values();
    }
}
