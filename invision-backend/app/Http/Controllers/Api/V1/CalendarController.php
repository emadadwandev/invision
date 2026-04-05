<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(protected CalendarService $service) {}

    // ─── Weeks ─────────────────────────────────────────────

    public function weeks(Request $request): JsonResponse
    {
        $year = $request->integer('year', now()->year);
        return response()->json(['data' => $this->service->getWeeks($year)]);
    }

    public function generateWeeks(Request $request): JsonResponse
    {
        $request->validate(['year' => 'required|integer|min:2020|max:2099']);
        $weeks = $this->service->generateWeeks($request->integer('year'));
        return response()->json(['data' => $weeks, 'count' => count($weeks)], 201);
    }

    // ─── Holidays ──────────────────────────────────────────

    public function holidays(Request $request): JsonResponse
    {
        $year = $request->has('year') ? $request->integer('year') : null;
        return response()->json(['data' => $this->service->getHolidays($year)]);
    }

    public function storeHoliday(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'date' => 'required|date',
            'type' => 'sometimes|in:public,company,regional',
            'description' => 'nullable|string',
            'is_recurring' => 'sometimes|boolean',
        ]);

        $holiday = $this->service->createHoliday($data);
        return response()->json(['data' => $holiday], 201);
    }

    public function updateHoliday(Request $request, int $id): JsonResponse
    {
        $holiday = \App\Models\Holiday::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:120',
            'date' => 'sometimes|date',
            'type' => 'sometimes|in:public,company,regional',
            'description' => 'nullable|string',
            'is_recurring' => 'sometimes|boolean',
        ]);

        $holiday = $this->service->updateHoliday($holiday, $data);
        return response()->json(['data' => $holiday]);
    }

    public function destroyHoliday(int $id): JsonResponse
    {
        $holiday = \App\Models\Holiday::findOrFail($id);
        $this->service->deleteHoliday($holiday);
        return response()->json(null, 204);
    }

    public function checkHoliday(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date']);
        return response()->json([
            'date' => $request->input('date'),
            'is_holiday' => $this->service->isHoliday($request->input('date')),
        ]);
    }

    // ─── Events ────────────────────────────────────────────

    public function events(Request $request): JsonResponse
    {
        $filters = $request->only(['from', 'to', 'type']);
        return response()->json(['data' => $this->service->getEvents($filters)]);
    }

    public function storeEvent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'all_day' => 'sometimes|boolean',
            'type' => 'sometimes|in:campaign,route,meeting,deadline,other',
            'color' => 'nullable|string|max:7',
            'eventable_type' => 'nullable|string',
            'eventable_id' => 'nullable|integer',
        ]);

        $event = $this->service->createEvent($data);
        return response()->json(['data' => $event], 201);
    }

    public function showEvent(int $id): JsonResponse
    {
        $event = \App\Models\CalendarEvent::with('creator')->findOrFail($id);
        return response()->json(['data' => $event]);
    }

    public function updateEvent(Request $request, int $id): JsonResponse
    {
        $event = \App\Models\CalendarEvent::findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'start_at' => 'sometimes|date',
            'end_at' => 'nullable|date',
            'all_day' => 'sometimes|boolean',
            'type' => 'sometimes|in:campaign,route,meeting,deadline,other',
            'color' => 'nullable|string|max:7',
        ]);

        $event = $this->service->updateEvent($event, $data);
        return response()->json(['data' => $event]);
    }

    public function destroyEvent(int $id): JsonResponse
    {
        $event = \App\Models\CalendarEvent::findOrFail($id);
        $this->service->deleteEvent($event);
        return response()->json(null, 204);
    }
}
