<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Route\CreateRoutePlanRequest;
use App\Http\Requests\Route\UpdateRoutePlanRequest;
use App\Models\RouteInstance;
use App\Models\RoutePlan;
use App\Models\Store;
use App\Models\User;
use App\Services\RouteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouteController extends Controller
{
    private const FIELD_ROLES = [
        UserRole::FieldForce,
        UserRole::Promoter,
        UserRole::Merchandiser,
        UserRole::SalesRepresentative,
    ];

    public function __construct(
        private readonly RouteService $routeService,
    ) {}

    // ─── Route Plans ──────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('viewAny', RoutePlan::class);

        $filters = $request->only(['search', 'status', 'assigned_to', 'frequency']);

        // Team leaders only see routes assigned to field force users
        if ($request->user()->hasRole(UserRole::TeamLeader)) {
            $filters['assigned_to_roles'] = self::FIELD_ROLES;
        }

        $plans = $this->routeService->listPlans(
            $filters,
            $request->integer('per_page', 15)
        );

        return view('pages.routes.index', compact('plans'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', RoutePlan::class);

        $userQuery = User::where('is_active', true);

        // Team leaders can only assign routes to field force users
        if ($request->user()->hasRole(UserRole::TeamLeader)) {
            $userQuery->whereIn('role', self::FIELD_ROLES);
        }

        $users = $userQuery->get();
        $stores = Store::where('is_active', true)->get();

        return view('pages.routes.create', compact('users', 'stores'));
    }

    public function store(CreateRoutePlanRequest $request): RedirectResponse
    {
        $this->authorize('create', RoutePlan::class);

        $this->routeService->createPlan($request->validated());

        return redirect()->route('routes.index')
            ->with('success', 'Route plan created successfully.');
    }

    public function show(RoutePlan $route): View
    {
        $this->authorize('view', $route);

        $route->load(['assignedUser', 'routeStores.store', 'instances' => function ($query) {
            $query->latest('route_date')->limit(20);
        }, 'instances.user']);

        return view('pages.routes.show', compact('route'));
    }

    public function edit(RoutePlan $route, Request $request): View
    {
        $this->authorize('update', $route);

        $route->load('routeStores.store');

        $userQuery = User::where('is_active', true);

        // Team leaders can only assign routes to field force users
        if ($request->user()->hasRole(UserRole::TeamLeader)) {
            $userQuery->whereIn('role', self::FIELD_ROLES);
        }

        $users = $userQuery->get();
        $stores = Store::where('is_active', true)->get();

        return view('pages.routes.edit', compact('route', 'users', 'stores'));
    }

    public function update(UpdateRoutePlanRequest $request, RoutePlan $route): RedirectResponse
    {
        $this->authorize('update', $route);

        $this->routeService->updatePlan($route, $request->validated());

        return redirect()->route('routes.show', $route)
            ->with('success', 'Route plan updated successfully.');
    }

    public function destroy(RoutePlan $route): RedirectResponse
    {
        $this->authorize('delete', $route);

        $this->routeService->deletePlan($route);

        return redirect()->route('routes.index')
            ->with('success', 'Route plan deleted successfully.');
    }

    // ─── Route Instances ──────────────────────────────────────

    public function instances(Request $request): View
    {
        $instances = $this->routeService->listInstances(
            $request->only(['user_id', 'route_date', 'date_from', 'date_to', 'status', 'route_plan_id']),
            $request->integer('per_page', 15)
        );

        return view('pages.routes.instances', compact('instances'));
    }

    public function showInstance(RouteInstance $routeInstance): View
    {
        $routeInstance->load(['routePlan', 'user', 'visits.store']);

        return view('pages.routes.instance-show', ['instance' => $routeInstance]);
    }

    public function createInstance(Request $request, RoutePlan $route): RedirectResponse
    {
        $this->authorize('update', $route);

        $request->validate(['route_date' => ['required', 'date']]);

        $date = $request->input('route_date');
        $instance = $this->routeService->createInstance($route, $date);

        $wasCreated = $instance->wasRecentlyCreated;

        return redirect()->route('routes.show', $route)
            ->with(
                'success',
                $wasCreated
                    ? "Route instance created for {$date}."
                    : "Route instance for {$date} has been synced with the latest stores."
            );
    }

    // ─── GPS Tracking (Live Map View) ─────────────────────────

    public function tracking(Request $request): View
    {
        $users = User::where('is_active', true)
            ->whereIn('role', ['promoter', 'merchandiser', 'field_force', 'sales_representative'])
            ->get();

        return view('pages.routes.tracking', compact('users'));
    }
}
