<?php

namespace App\Models;

use App\Enums\RouteStatus;
use App\Enums\VisitFrequency;
use App\Models\Concerns\HasTenant;
use App\Services\GeoFenceService;
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
        'optimize_order',
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
            'optimize_order' => 'boolean',
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

    /** Plan rows regrouped for the edit form: one entry per store/week with its visit days. */
    public function formRows(): Collection
    {
        return $this->routeStores
            ->groupBy(fn (RoutePlanStore $r) => implode('|', [$r->store_id, $r->week_number, $r->week_start?->toDateString(), $r->expected_duration_minutes, $r->visit_order]))
            ->map(fn (Collection $rows) => [
                'store_id' => (string) $rows->first()->store_id,
                'days' => $rows->pluck('day_of_week')->filter()->values()->all(),
                'week_number' => (string) ($rows->first()->week_number ?? ''),
                'week_start' => $rows->first()->week_start?->toDateString() ?? '',
                'duration' => $rows->first()->expected_duration_minutes ?? '',
            ])
            ->values();
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

        $day = $pool
            ->filter(fn (RoutePlanStore $r) => $r->day_of_week ? $r->day_of_week === $dow : $isWorkDay)
            ->filter(fn (RoutePlanStore $r) => ! $usesCycle || ! $r->week_number || $r->week_number === $cycleWeek)
            ->sortBy('visit_order')
            ->unique('store_id')
            ->values();

        return $this->optimize_order ? self::optimizeOrder($day) : $day;
    }

    /**
     * Shortest-path order starting at the first row (nearest neighbour + 2-opt).
     * Rows whose store has no GPS keep their relative order at the end.
     */
    public static function optimizeOrder(Collection $rows): Collection
    {
        [$located, $unlocated] = $rows->partition(fn (RoutePlanStore $r) => $r->store?->gps_latitude !== null && $r->store?->gps_longitude !== null);
        $points = $located->values()->all();
        if (count($points) < 3) {
            return $located->values()->concat($unlocated)->values();
        }

        $dist = fn (RoutePlanStore $a, RoutePlanStore $b) => GeoFenceService::calculateDistance(
            (float) $a->store->gps_latitude, (float) $a->store->gps_longitude,
            (float) $b->store->gps_latitude, (float) $b->store->gps_longitude,
        );

        // Nearest neighbour from the fixed start
        $route = [array_shift($points)];
        while ($points) {
            $last = end($route);
            usort($points, fn ($a, $b) => $dist($last, $a) <=> $dist($last, $b));
            $route[] = array_shift($points);
        }

        // 2-opt: reverse segments while it shortens the open path (start stays fixed)
        // ponytail: O(n^3) worst case, fine for a rep's daily stores (< ~60); use a solver beyond that
        $n = count($route);
        do {
            $improved = false;
            for ($i = 1; $i < $n - 1; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $before = $dist($route[$i - 1], $route[$i]) + ($j + 1 < $n ? $dist($route[$j], $route[$j + 1]) : 0);
                    $after = $dist($route[$i - 1], $route[$j]) + ($j + 1 < $n ? $dist($route[$i], $route[$j + 1]) : 0);
                    if ($after + 0.01 < $before) {
                        array_splice($route, $i, $j - $i + 1, array_reverse(array_slice($route, $i, $j - $i + 1)));
                        $improved = true;
                    }
                }
            }
        } while ($improved);

        return collect($route)->concat($unlocated)->values();
    }

    /** Straight-line travel distance (km) visiting rows in order. */
    public static function pathKm(Collection $rows): float
    {
        $located = $rows->filter(fn (RoutePlanStore $r) => $r->store?->gps_latitude !== null && $r->store?->gps_longitude !== null)->values();
        $meters = 0;
        for ($i = 1; $i < $located->count(); $i++) {
            $meters += GeoFenceService::calculateDistance(
                (float) $located[$i - 1]->store->gps_latitude, (float) $located[$i - 1]->store->gps_longitude,
                (float) $located[$i]->store->gps_latitude, (float) $located[$i]->store->gps_longitude,
            );
        }

        return round($meters / 1000, 1);
    }
}
