<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\CreditAccount;
use App\Models\GpsTrackingLog;
use App\Models\Payment;
use App\Models\PosTransaction;
use App\Models\RouteInstance;
use App\Models\SalesOrder;
use App\Models\Store;
use App\Models\StoreInventory;
use App\Models\StoreVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Overview KPIs for the main dashboard.
     */
    public function getOverviewKpis(): array
    {
        $today = Carbon::today();

        return [
            'total_users' => User::where('is_active', true)->count(),
            'field_force_count' => User::whereIn('role', [
                UserRole::Promoter->value,
                UserRole::Merchandiser->value,
                UserRole::FieldForce->value,
                UserRole::SalesRepresentative->value,
            ])->where('is_active', true)->count(),
            'online_now' => GpsTrackingLog::where('recorded_at', '>=', now()->subMinutes(30))
                ->distinct('user_id')->count('user_id'),
            'total_stores' => Store::where('is_active', true)->count(),
            'active_campaigns' => Campaign::where('status', 'active')->count(),
            'today_visits' => StoreVisit::whereDate('checked_in_at', $today)->count(),
            'today_orders' => SalesOrder::whereDate('created_at', $today)->count(),
            'today_sales' => (float) SalesOrder::whereDate('created_at', $today)->sum('total_amount'),
            'today_collections' => (float) Payment::whereDate('paid_at', $today)->sum('amount'),
            'active_routes' => RouteInstance::whereDate('started_at', $today)
                ->where('status', 'in_progress')->count(),
        ];
    }

    /**
     * Sales performance KPIs.
     */
    public function getSalesKpis(?string $period = 'month'): array
    {
        $start = match ($period) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $orders = SalesOrder::where('created_at', '>=', $start);
        $delivered = (clone $orders)->where('status', OrderStatus::Delivered->value);

        $totalRevenue = (float) $delivered->sum('total_amount');
        $totalOrders = $orders->count();
        $deliveredCount = $delivered->count();
        $cancelledCount = (clone $orders)->where('status', OrderStatus::Cancelled->value)->count();

        // Top stores by sales
        $topStores = SalesOrder::where('created_at', '>=', $start)
            ->where('status', OrderStatus::Delivered->value)
            ->select('store_id', DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as order_count'))
            ->groupBy('store_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->with('store:id,name,code')
            ->get()
            ->map(fn ($row) => [
                'store_id' => $row->store_id,
                'store_name' => $row->store?->name,
                'store_code' => $row->store?->code,
                'total_sales' => (float) $row->total_sales,
                'order_count' => (int) $row->order_count,
            ]);

        // Top sales reps
        $topReps = SalesOrder::where('created_at', '>=', $start)
            ->where('status', OrderStatus::Delivered->value)
            ->select('user_id', DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as order_count'))
            ->groupBy('user_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->with('salesperson:id,first_name,last_name')
            ->get()
            ->map(fn ($row) => [
                'user_id' => $row->user_id,
                'name' => $row->salesperson?->full_name,
                'total_sales' => (float) $row->total_sales,
                'order_count' => (int) $row->order_count,
            ]);

        // Daily sales trend
        $dailySales = SalesOrder::where('created_at', '>=', $start)
            ->where('status', OrderStatus::Delivered->value)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ]);

        return [
            'period' => $period,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'delivered_count' => $deliveredCount,
            'cancelled_count' => $cancelledCount,
            'avg_order_value' => $deliveredCount > 0 ? round($totalRevenue / $deliveredCount, 2) : 0,
            'top_stores' => $topStores,
            'top_sales_reps' => $topReps,
            'daily_trend' => $dailySales,
        ];
    }

    /**
     * Route & visit performance KPIs.
     */
    public function getRouteKpis(?string $period = 'month'): array
    {
        $start = match ($period) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            default => Carbon::now()->startOfMonth(),
        };

        $instances = RouteInstance::where('started_at', '>=', $start);
        $totalInstances = $instances->count();
        $completedInstances = (clone $instances)->where('status', 'completed')->count();

        $visits = StoreVisit::where('checked_in_at', '>=', $start);
        $totalVisits = $visits->count();
        $completedVisits = (clone $visits)->where('status', 'completed')->count();
        $skippedVisits = (clone $visits)->where('status', 'skipped')->count();
        $avgDuration = (clone $visits)->where('status', 'completed')->avg('duration_minutes');

        // Visits per user
        $visitsPerUser = StoreVisit::where('checked_in_at', '>=', $start)
            ->where('status', 'completed')
            ->select('user_id', DB::raw('COUNT(*) as visit_count'), DB::raw('AVG(duration_minutes) as avg_duration'))
            ->groupBy('user_id')
            ->orderByDesc('visit_count')
            ->limit(10)
            ->with('user:id,first_name,last_name')
            ->get()
            ->map(fn ($row) => [
                'user_id' => $row->user_id,
                'name' => $row->user?->full_name,
                'visit_count' => (int) $row->visit_count,
                'avg_duration' => round((float) $row->avg_duration, 1),
            ]);

        return [
            'period' => $period,
            'total_route_instances' => $totalInstances,
            'completed_instances' => $completedInstances,
            'completion_rate' => $totalInstances > 0 ? round(($completedInstances / $totalInstances) * 100, 1) : 0,
            'total_visits' => $totalVisits,
            'completed_visits' => $completedVisits,
            'skipped_visits' => $skippedVisits,
            'visit_completion_rate' => $totalVisits > 0 ? round(($completedVisits / $totalVisits) * 100, 1) : 0,
            'avg_visit_duration' => round((float) ($avgDuration ?? 0), 1),
            'top_performers' => $visitsPerUser,
        ];
    }

    /**
     * Campaign performance KPIs.
     */
    public function getCampaignKpis(): array
    {
        $activeCampaigns = Campaign::where('status', 'active')->get();
        $totalBudget = $activeCampaigns->sum('budget');
        $totalSpent = $activeCampaigns->sum('spent');

        $campaignSummary = Campaign::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $campaignPerformance = Campaign::whereIn('status', ['active', 'completed'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type->value,
                'status' => $c->status->value,
                'budget' => (float) $c->budget,
                'spent' => (float) $c->spent,
                'utilization' => $c->budgetUtilization(),
                'total_tasks' => $c->tasks_count,
                'completed_tasks' => $c->completed_tasks_count,
                'task_completion' => $c->tasks_count > 0 ? round(($c->completed_tasks_count / $c->tasks_count) * 100, 1) : 0,
            ]);

        return [
            'status_summary' => $campaignSummary,
            'total_budget' => (float) $totalBudget,
            'total_spent' => (float) $totalSpent,
            'budget_utilization' => $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0,
            'campaign_performance' => $campaignPerformance,
        ];
    }

    /**
     * Store inquiry data with filters.
     */
    public function getStoreInquiry(array $filters = []): Collection
    {
        $query = Store::with(['area', 'contacts' => fn ($q) => $q->where('is_primary', true)])
            ->where('is_active', true);

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (! empty($filters['rank'])) {
            $query->where('rank', $filters['rank']);
        }
        if (! empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->get()->map(function ($store) {
            $salesTotal = (float) SalesOrder::where('store_id', $store->id)
                ->where('status', OrderStatus::Delivered->value)
                ->sum('total_amount');
            $orderCount = SalesOrder::where('store_id', $store->id)->count();
            $stockCount = StoreInventory::where('store_id', $store->id)
                ->sum(DB::raw('on_shelf_quantity + warehouse_quantity'));
            $credit = CreditAccount::where('store_id', $store->id)->first();

            return [
                'id' => $store->id,
                'name' => $store->name,
                'code' => $store->code,
                'category' => $store->category?->value,
                'rank' => $store->rank?->value,
                'area' => $store->area?->name,
                'address' => $store->address,
                'primary_contact' => $store->contacts->first()?->phone,
                'order_count' => $orderCount,
                'total_sales' => $salesTotal,
                'stock_quantity' => (int) $stockCount,
                'credit_limit' => $credit ? (float) $credit->credit_limit : null,
                'credit_balance' => $credit ? (float) $credit->current_balance : null,
                'available_credit' => $credit?->availableCredit(),
            ];
        });
    }

    /**
     * Sales inquiry data with filters.
     */
    public function getSalesInquiry(array $filters = []): Collection
    {
        $query = SalesOrder::with(['store:id,name,code', 'salesperson:id,first_name,last_name']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (! empty($filters['search'])) {
            $query->where('order_number', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->limit(100)->get()->map(fn ($order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'store_name' => $order->store?->name,
            'store_code' => $order->store?->code,
            'salesperson' => $order->salesperson?->full_name,
            'status' => $order->status->value,
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount_amount,
            'tax' => (float) $order->tax_amount,
            'total' => (float) $order->total_amount,
            'paid' => (float) $order->totalPaid(),
            'balance_due' => (float) $order->balanceDue(),
            'created_at' => $order->created_at->toDateString(),
        ]);
    }

    /**
     * Route inquiry data with filters.
     */
    public function getRouteInquiry(array $filters = []): Collection
    {
        $query = RouteInstance::with(['routePlan:id,name', 'user:id,first_name,last_name']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('started_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('started_at', '<=', $filters['date_to']);
        }

        return $query->latest('started_at')->limit(100)->get()->map(fn ($inst) => [
            'id' => $inst->id,
            'route_name' => $inst->routePlan?->name,
            'user' => $inst->user?->full_name,
            'status' => $inst->status->value,
            'route_date' => $inst->route_date,
            'started_at' => $inst->started_at?->toDateTimeString(),
            'completed_at' => $inst->completed_at?->toDateTimeString(),
            'total_visits' => $inst->total_visits,
            'completed_visits' => $inst->completed_visits,
            'completion_pct' => $inst->total_visits > 0 ? round(($inst->completed_visits / $inst->total_visits) * 100, 1) : 0,
            'distance_km' => $inst->total_distance_km,
        ]);
    }

    /**
     * POS performance summary.
     */
    public function getPosKpis(?string $period = 'month'): array
    {
        $start = match ($period) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            default => Carbon::now()->startOfMonth(),
        };

        $transactions = PosTransaction::where('created_at', '>=', $start);
        $completedTx = (clone $transactions)->where('status', 'completed');

        return [
            'period' => $period,
            'total_transactions' => $transactions->count(),
            'completed_transactions' => $completedTx->count(),
            'total_amount' => (float) $completedTx->sum('total_amount'),
            'sell_out_amount' => (float) (clone $completedTx)->where('type', 'sell_out')->sum('total_amount'),
            'sell_through_amount' => (float) (clone $completedTx)->where('type', 'sell_through')->sum('total_amount'),
        ];
    }

    /**
     * Credit & collections overview.
     */
    public function getCreditKpis(): array
    {
        $accounts = CreditAccount::all();
        $totalLimit = $accounts->sum('credit_limit');
        $totalBalance = $accounts->sum('current_balance');
        $totalAvailable = $accounts->sum(fn ($a) => $a->availableCredit());

        $topDebtors = CreditAccount::with('store:id,name,code')
            ->orderByDesc('current_balance')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'store_name' => $a->store?->name,
                'store_code' => $a->store?->code,
                'credit_limit' => (float) $a->credit_limit,
                'balance' => (float) $a->current_balance,
                'available' => $a->availableCredit(),
                'utilization' => $a->credit_limit > 0 ? round(($a->current_balance / $a->credit_limit) * 100, 1) : 0,
            ]);

        $recentPayments = Payment::with(['salesOrder.store:id,name'])
            ->where('status', 'paid')
            ->latest('paid_at')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'store_name' => $p->salesOrder?->store?->name,
                'method' => $p->payment_method->value,
                'amount' => (float) $p->amount,
                'paid_at' => $p->paid_at?->toDateString(),
            ]);

        return [
            'total_credit_limit' => (float) $totalLimit,
            'total_balance' => (float) $totalBalance,
            'total_available' => (float) $totalAvailable,
            'utilization_pct' => $totalLimit > 0 ? round(($totalBalance / $totalLimit) * 100, 1) : 0,
            'accounts_count' => $accounts->count(),
            'top_debtors' => $topDebtors,
            'recent_payments' => $recentPayments,
        ];
    }
}
