<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\CalendarWeek;
use App\Models\Holiday;
use Carbon\Carbon;

class CalendarService
{
    public function generateWeeks(int $year): array
    {
        $tenantId = app('current_tenant_id');
        $weeks = [];
        $startOfYear = Carbon::create($year, 1, 1)->startOfWeek();

        for ($w = 1; $w <= 52; $w++) {
            $start = $startOfYear->copy()->addWeeks($w - 1);
            $end = $start->copy()->endOfWeek();

            $weeks[] = CalendarWeek::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'year' => $year,
                    'week_number' => $w,
                ],
                [
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'label' => "W{$w} {$year}",
                    'is_active' => true,
                ]
            );
        }

        return $weeks;
    }

    public function getWeeks(int $year): mixed
    {
        return CalendarWeek::where('year', $year)
            ->orderBy('week_number')
            ->get();
    }

    public function getHolidays(?int $year = null): mixed
    {
        $query = Holiday::query();

        if ($year) {
            $query->whereYear('date', $year)
                ->orWhere('is_recurring', true);
        }

        return $query->orderBy('date')->get();
    }

    public function createHoliday(array $data): Holiday
    {
        $data['tenant_id'] = app('current_tenant_id');
        return Holiday::create($data);
    }

    public function updateHoliday(Holiday $holiday, array $data): Holiday
    {
        $holiday->update($data);
        return $holiday->fresh();
    }

    public function deleteHoliday(Holiday $holiday): void
    {
        $holiday->delete();
    }

    public function getEvents(array $filters = []): mixed
    {
        $query = CalendarEvent::with('creator');

        if (!empty($filters['from'])) {
            $query->where('start_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('end_at', '<=', $filters['to'])
                    ->orWhereNull('end_at');
            });
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy('start_at')->get();
    }

    public function createEvent(array $data): CalendarEvent
    {
        $data['tenant_id'] = app('current_tenant_id');
        $data['created_by'] = auth()->id();
        return CalendarEvent::create($data);
    }

    public function updateEvent(CalendarEvent $event, array $data): CalendarEvent
    {
        $event->update($data);
        return $event->fresh();
    }

    public function deleteEvent(CalendarEvent $event): void
    {
        $event->delete();
    }

    /**
     * Check if a date is a holiday.
     */
    public function isHoliday(string $date): bool
    {
        $carbon = Carbon::parse($date);

        return Holiday::where('date', $carbon->toDateString())
            ->orWhere(function ($q) use ($carbon) {
                $q->where('is_recurring', true)
                    ->whereMonth('date', $carbon->month)
                    ->whereDay('date', $carbon->day);
            })
            ->exists();
    }

    /**
     * Get the business week for a given date.
     */
    public function getWeekForDate(string $date): ?CalendarWeek
    {
        $carbon = Carbon::parse($date);

        return CalendarWeek::where('year', $carbon->year)
            ->where('start_date', '<=', $carbon->toDateString())
            ->where('end_date', '>=', $carbon->toDateString())
            ->first();
    }
}
