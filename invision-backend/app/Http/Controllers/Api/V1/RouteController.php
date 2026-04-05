<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RouteStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Route\CheckInRequest;
use App\Http\Requests\Route\CheckOutRequest;
use App\Http\Requests\Route\CreateRoutePlanRequest;
use App\Http\Requests\Route\GpsLogRequest;
use App\Http\Requests\Route\UpdateRoutePlanRequest;
use App\Http\Resources\GpsTrackingLogResource;
use App\Http\Resources\RouteInstanceResource;
use App\Http\Resources\RoutePlanResource;
use App\Http\Resources\StoreVisitResource;
use App\Models\RouteInstance;
use App\Models\RoutePlan;
use App\Models\StoreVisit;
use App\Services\RouteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RouteController extends Controller
{
    public function __construct(
        private readonly RouteService $routeService,
    ) {}

    // ─── Route Plans ──────────────────────────────────────────

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', RoutePlan::class);

        $plans = $this->routeService->listPlans(
            $request->only(['search', 'status', 'assigned_to', 'frequency']),
            $request->integer('per_page', 15)
        );

        return RoutePlanResource::collection($plans);
    }

    public function store(CreateRoutePlanRequest $request): JsonResponse
    {
        $this->authorize('create', RoutePlan::class);

        $plan = $this->routeService->createPlan($request->validated());

        return (new RoutePlanResource($plan))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RoutePlan $routePlan): RoutePlanResource
    {
        $this->authorize('view', $routePlan);

        $routePlan->load(['assignedUser', 'routeStores.store', 'instances']);

        return new RoutePlanResource($routePlan);
    }

    public function update(UpdateRoutePlanRequest $request, RoutePlan $routePlan): RoutePlanResource
    {
        $this->authorize('update', $routePlan);

        $plan = $this->routeService->updatePlan($routePlan, $request->validated());

        return new RoutePlanResource($plan);
    }

    public function destroy(RoutePlan $routePlan): JsonResponse
    {
        $this->authorize('delete', $routePlan);

        $this->routeService->deletePlan($routePlan);

        return response()->json(null, 204);
    }

    public function addStore(Request $request, RoutePlan $routePlan): JsonResponse
    {
        $this->authorize('update', $routePlan);

        $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'visit_order' => ['nullable', 'integer', 'min:1'],
            'expected_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $routeStore = $this->routeService->addStoreToPlan($routePlan, $request->all());

        return response()->json(['data' => $routeStore], 201);
    }

    public function removeStore(RoutePlan $routePlan, int $storeId): JsonResponse
    {
        $this->authorize('update', $routePlan);

        $this->routeService->removeStoreFromPlan($routePlan, $storeId);

        return response()->json(null, 204);
    }

    public function reorderStores(Request $request, RoutePlan $routePlan): JsonResponse
    {
        $this->authorize('update', $routePlan);

        $request->validate([
            'orders' => ['required', 'array'],
            'orders.*.store_id' => ['required', 'exists:stores,id'],
            'orders.*.visit_order' => ['required', 'integer', 'min:1'],
        ]);

        $this->routeService->reorderPlanStores($routePlan, $request->input('orders'));

        return response()->json(['message' => 'Stores reordered successfully.']);
    }

    // ─── Route Instances ──────────────────────────────────────

    public function instances(Request $request): AnonymousResourceCollection
    {
        $instances = $this->routeService->listInstances(
            $request->only(['user_id', 'route_date', 'date_from', 'date_to', 'status', 'route_plan_id']),
            $request->integer('per_page', 15)
        );

        return RouteInstanceResource::collection($instances);
    }

    public function createInstance(Request $request, RoutePlan $routePlan): JsonResponse
    {
        $this->authorize('update', $routePlan);

        $request->validate([
            'route_date' => ['required', 'date'],
        ]);

        $instance = $this->routeService->createInstance($routePlan, $request->input('route_date'));

        return (new RouteInstanceResource($instance))
            ->response()
            ->setStatusCode(201);
    }

    public function showInstance(RouteInstance $routeInstance): RouteInstanceResource
    {
        $routeInstance->load(['routePlan', 'user', 'visits.store']);

        return new RouteInstanceResource($routeInstance);
    }

    public function startInstance(RouteInstance $routeInstance): RouteInstanceResource
    {
        $instance = $this->routeService->startRoute($routeInstance);

        return new RouteInstanceResource($instance);
    }

    public function completeInstance(RouteInstance $routeInstance): RouteInstanceResource
    {
        $instance = $this->routeService->completeRoute($routeInstance);

        return new RouteInstanceResource($instance);
    }

    // ─── Store Visits ─────────────────────────────────────────

    public function checkIn(CheckInRequest $request, StoreVisit $storeVisit): StoreVisitResource|JsonResponse
    {
        try {
            $visit = $this->routeService->checkIn($storeVisit, $request->validated());
            return new StoreVisitResource($visit->load('store'));
        } catch (\App\Exceptions\GeoFenceException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'geofence' => $e->toArray(),
            ], 422);
        }
    }

    public function checkOut(CheckOutRequest $request, StoreVisit $storeVisit): StoreVisitResource
    {
        $visit = $this->routeService->checkOut($storeVisit, $request->validated());

        return new StoreVisitResource($visit->load('store'));
    }

    public function skipVisit(Request $request, StoreVisit $storeVisit): StoreVisitResource
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $visit = $this->routeService->skipVisit($storeVisit, $request->input('reason'));

        return new StoreVisitResource($visit->load('store'));
    }

    // ─── GPS Tracking ─────────────────────────────────────────

    public function logGps(GpsLogRequest $request): JsonResponse
    {
        $user = $request->user();

        // Batch logging
        if ($request->has('logs')) {
            $logs = collect($request->input('logs'))->map(fn ($log) => array_merge($log, [
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
            ]))->all();

            $count = $this->routeService->batchLogGps($logs);

            return response()->json(['message' => "{$count} GPS logs recorded."]);
        }

        // Single log
        $log = $this->routeService->logGps(array_merge($request->validated(), [
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
        ]));

        return (new GpsTrackingLogResource($log))
            ->response()
            ->setStatusCode(201);
    }

    public function trackingLogs(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
        ]);

        $logs = $this->routeService->getUserTrackingLogs(
            $request->integer('user_id'),
            $request->input('date')
        );

        return GpsTrackingLogResource::collection($logs);
    }

    // ─── My Routes (Mobile) ──────────────────────────────────

    public function myRouteToday(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = now()->toDateString();

        $instance = RouteInstance::with(['routePlan', 'visits.store'])
            ->where('user_id', $user->id)
            ->whereDate('route_date', $today)
            ->first();

        // Auto-create an instance from the assigned plan if none exists for today
        if (! $instance) {
            $plan = RoutePlan::with('routeStores')
                ->where('assigned_to', $user->id)
                ->where('status', RouteStatus::Published)
                ->whereDate('start_date', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $today);
                })
                ->first();

            if ($plan) {
                $instance = $this->routeService->createInstance($plan, $today);
            }
        }

        if (! $instance) {
            return response()->json(['data' => null, 'message' => 'No route assigned for today.']);
        }

        return (new RouteInstanceResource($instance))->response();
    }

    public function myRoutes(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $instances = RouteInstance::with(['routePlan', 'visits.store'])
            ->where('user_id', $user->id)
            ->when($request->input('date_from'), fn ($q, $from) => $q->whereDate('route_date', '>=', $from))
            ->when($request->input('date_to'), fn ($q, $to) => $q->whereDate('route_date', '<=', $to))
            ->latest('route_date')
            ->paginate($request->integer('per_page', 15));

        return RouteInstanceResource::collection($instances);
    }
}
