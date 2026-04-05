<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\CreditAccount;
use App\Models\GpsTrackingLog;
use App\Models\RouteInstance;
use App\Models\SalesOrder;
use App\Models\Store;
use App\Models\StoreInventory;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommandCenterService
{
    /**
     * Get latest GPS positions for all field force users.
     */
    public function getFieldForcePositions(): Collection
    {
        $fieldForceRoles = [
            UserRole::Promoter->value,
            UserRole::Merchandiser->value,
            UserRole::FieldForce->value,
            UserRole::SalesRepresentative->value,
        ];

        // Get latest GPS log per user using a subquery
        $latestLogs = GpsTrackingLog::select('user_id', DB::raw('MAX(id) as latest_id'))
            ->groupBy('user_id')
            ->pluck('latest_id');

        $gpsLogs = GpsTrackingLog::with('user')
            ->whereIn('id', $latestLogs)
            ->get()
            ->keyBy('user_id');

        $users = User::whereIn('role', $fieldForceRoles)
            ->where('is_active', true)
            ->get();

        return $users->map(function ($user) use ($gpsLogs) {
            $log = $gpsLogs->get($user->id);
            $isOnline = $log && $log->recorded_at && $log->recorded_at->diffInMinutes(now()) < 30;

            return [
                'id' => $user->id,
                'name' => $user->full_name,
                'role' => $user->role->value,
                'role_label' => $user->role->label(),
                'is_online' => $isOnline,
                'latitude' => $log ? (float) $log->latitude : null,
                'longitude' => $log ? (float) $log->longitude : null,
                'speed_kmh' => $log?->speed_kmh !== null ? (float) $log->speed_kmh : null,
                'last_seen' => $log?->recorded_at?->toIso8601String(),
                'route_instance_id' => $log?->route_instance_id,
            ];
        });
    }

    /**
     * Get all stores with GPS coordinates and summary data.
     */
    public function getStoreMapData(): Collection
    {
        $stores = Store::whereNotNull('gps_latitude')
            ->whereNotNull('gps_longitude')
            ->where('is_active', true)
            ->get();

        return $stores->map(function ($store) {
            // Get recent sales summary
            $salesSummary = SalesOrder::where('store_id', $store->id)
                ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as total_sales')
                ->first();

            // Get inventory count
            $inventoryCount = StoreInventory::where('store_id', $store->id)
                ->selectRaw('COUNT(*) as product_count, COALESCE(SUM(on_shelf_quantity + warehouse_quantity), 0) as total_stock')
                ->first();

            // Get credit info
            $credit = CreditAccount::where('store_id', $store->id)->first();

            return [
                'id' => $store->id,
                'name' => $store->name,
                'code' => $store->code,
                'category' => $store->category?->value,
                'rank' => $store->rank?->value,
                'address' => $store->address,
                'latitude' => (float) $store->gps_latitude,
                'longitude' => (float) $store->gps_longitude,
                'sales' => [
                    'order_count' => (int) $salesSummary->order_count,
                    'total_sales' => (float) $salesSummary->total_sales,
                ],
                'inventory' => [
                    'product_count' => (int) $inventoryCount->product_count,
                    'total_stock' => (int) $inventoryCount->total_stock,
                ],
                'credit' => $credit ? [
                    'credit_limit' => (float) $credit->credit_limit,
                    'current_balance' => (float) $credit->current_balance,
                    'available_credit' => $credit->availableCredit(),
                ] : null,
            ];
        });
    }

    /**
     * Get detailed store inquiry data.
     */
    public function getStoreInquiry(int $storeId): array
    {
        $store = Store::with(['contacts', 'area'])->findOrFail($storeId);

        // Recent orders
        $recentOrders = SalesOrder::where('store_id', $storeId)
            ->with('salesperson:id,first_name,last_name')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status->value,
                'total_amount' => (float) $o->total_amount,
                'salesperson' => $o->salesperson?->full_name,
                'created_at' => $o->created_at->toIso8601String(),
            ]);

        // Inventory
        $inventory = StoreInventory::where('store_id', $storeId)
            ->with('product:id,name,sku')
            ->get()
            ->map(fn ($i) => [
                'product_name' => $i->product?->name,
                'sku' => $i->product?->sku,
                'on_shelf' => $i->on_shelf_quantity,
                'warehouse' => $i->warehouse_quantity,
                'total' => $i->totalQuantity(),
            ]);

        // Credit account
        $credit = CreditAccount::where('store_id', $storeId)->first();

        // Assigned field force (users who have route plans visiting this store)
        $assignedUsers = User::whereHas('teams', function ($q) {
        })->whereIn('role', [
            UserRole::Promoter->value,
            UserRole::Merchandiser->value,
            UserRole::FieldForce->value,
            UserRole::SalesRepresentative->value,
        ])->limit(10)->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->full_name,
            'role' => $u->role->label(),
        ]);

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'code' => $store->code,
                'category' => $store->category?->value,
                'rank' => $store->rank?->value,
                'address' => $store->address,
                'latitude' => (float) $store->gps_latitude,
                'longitude' => (float) $store->gps_longitude,
                'area' => $store->area?->name,
                'contacts' => $store->contacts->map(fn ($c) => [
                    'name' => $c->name,
                    'phone' => $c->phone,
                    'is_primary' => $c->is_primary,
                ]),
            ],
            'recent_orders' => $recentOrders,
            'inventory' => $inventory,
            'credit' => $credit ? [
                'credit_limit' => (float) $credit->credit_limit,
                'current_balance' => (float) $credit->current_balance,
                'available_credit' => $credit->availableCredit(),
                'last_payment_at' => $credit->last_payment_at?->toIso8601String(),
            ] : null,
            'assigned_field_force' => $assignedUsers,
        ];
    }

    /**
     * Get a user's current route activity for today.
     */
    public function getUserActivity(int $userId): array
    {
        $user = User::findOrFail($userId);

        $activeInstance = RouteInstance::with(['routePlan', 'visits.store'])
            ->where('user_id', $userId)
            ->whereDate('started_at', today())
            ->latest()
            ->first();

        // Today's GPS trail
        $gpsTrail = GpsTrackingLog::where('user_id', $userId)
            ->whereDate('recorded_at', today())
            ->orderBy('recorded_at')
            ->get()
            ->map(fn ($g) => [
                'latitude' => (float) $g->latitude,
                'longitude' => (float) $g->longitude,
                'speed_kmh' => $g->speed_kmh,
                'recorded_at' => $g->recorded_at->toIso8601String(),
            ]);

        $visits = $activeInstance?->visits->map(fn ($v) => [
            'store_id' => $v->store_id,
            'store_name' => $v->store?->name,
            'status' => $v->status->value,
            'checkin_at' => $v->checked_in_at?->toIso8601String(),
            'checkout_at' => $v->checked_out_at?->toIso8601String(),
            'duration_minutes' => $v->duration_minutes,
        ]) ?? collect();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'role' => $user->role->label(),
            ],
            'route' => $activeInstance ? [
                'id' => $activeInstance->id,
                'plan_name' => $activeInstance->routePlan?->name,
                'status' => $activeInstance->status->value,
                'started_at' => $activeInstance->started_at?->toIso8601String(),
            ] : null,
            'visits' => $visits,
            'gps_trail' => $gpsTrail,
        ];
    }

    /**
     * Dashboard stats for the command center.
     */
    public function getDashboardStats(): array
    {
        $fieldForceRoles = [
            UserRole::Promoter->value,
            UserRole::Merchandiser->value,
            UserRole::FieldForce->value,
            UserRole::SalesRepresentative->value,
        ];

        $totalFieldForce = User::whereIn('role', $fieldForceRoles)->where('is_active', true)->count();

        // Online = logged GPS in last 30 minutes
        $onlineCount = GpsTrackingLog::whereIn('user_id', function ($q) use ($fieldForceRoles) {
            $q->select('id')->from('users')->whereIn('role', $fieldForceRoles)->where('is_active', true);
        })
            ->where('recorded_at', '>=', now()->subMinutes(30))
            ->distinct('user_id')
            ->count('user_id');

        $activeRoutes = RouteInstance::whereDate('started_at', today())
            ->where('status', 'in_progress')
            ->count();

        $totalStores = Store::where('is_active', true)->count();

        $todaySales = SalesOrder::whereDate('created_at', today())
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
            ->first();

        return [
            'total_field_force' => $totalFieldForce,
            'online_count' => $onlineCount,
            'active_routes' => $activeRoutes,
            'total_stores' => $totalStores,
            'today_orders' => (int) $todaySales->count,
            'today_sales' => (float) $todaySales->total,
        ];
    }
}
