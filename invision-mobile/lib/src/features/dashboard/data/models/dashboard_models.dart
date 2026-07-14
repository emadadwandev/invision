class OverviewKpi {
  const OverviewKpi({
    required this.totalUsers,
    required this.fieldForceCount,
    required this.onlineNow,
    required this.totalStores,
    required this.activeCampaigns,
    required this.todayVisits,
    required this.todayOrders,
    required this.todaySales,
    required this.todayCollections,
    required this.activeRoutes,
  });

  final int totalUsers;
  final int fieldForceCount;
  final int onlineNow;
  final int totalStores;
  final int activeCampaigns;
  final int todayVisits;
  final int todayOrders;
  final double todaySales;
  final double todayCollections;
  final int activeRoutes;

  factory OverviewKpi.fromJson(Map<String, dynamic> json) {
    return OverviewKpi(
      totalUsers: json['total_users'] as int? ?? 0,
      fieldForceCount: json['field_force_count'] as int? ?? 0,
      onlineNow: json['online_now'] as int? ?? 0,
      totalStores: json['total_stores'] as int? ?? 0,
      activeCampaigns: json['active_campaigns'] as int? ?? 0,
      todayVisits: json['today_visits'] as int? ?? 0,
      todayOrders: json['today_orders'] as int? ?? 0,
      todaySales: (json['today_sales'] as num?)?.toDouble() ?? 0,
      todayCollections: (json['today_collections'] as num?)?.toDouble() ?? 0,
      activeRoutes: json['active_routes'] as int? ?? 0,
    );
  }
}

class SalesKpi {
  const SalesKpi({
    required this.period,
    required this.totalRevenue,
    required this.totalOrders,
    required this.deliveredCount,
    required this.cancelledCount,
    required this.avgOrderValue,
    required this.topStores,
    required this.topSalesReps,
  });

  final String period;
  final double totalRevenue;
  final int totalOrders;
  final int deliveredCount;
  final int cancelledCount;
  final double avgOrderValue;
  final List<RankedItem> topStores;
  final List<RankedItem> topSalesReps;

  factory SalesKpi.fromJson(Map<String, dynamic> json) {
    return SalesKpi(
      period: json['period'] as String? ?? 'month',
      totalRevenue: (json['total_revenue'] as num?)?.toDouble() ?? 0,
      totalOrders: json['total_orders'] as int? ?? 0,
      deliveredCount: json['delivered_count'] as int? ?? 0,
      cancelledCount: json['cancelled_count'] as int? ?? 0,
      avgOrderValue: (json['avg_order_value'] as num?)?.toDouble() ?? 0,
      topStores: (json['top_stores'] as List?)
              ?.map((e) => RankedItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      topSalesReps: (json['top_sales_reps'] as List?)
              ?.map((e) => RankedItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}

class RouteKpi {
  const RouteKpi({
    required this.completionRate,
    required this.totalRouteInstances,
    required this.completedInstances,
    required this.visitCompletionRate,
    required this.totalVisits,
    required this.completedVisits,
    required this.skippedVisits,
    required this.avgVisitDuration,
  });

  final double completionRate;
  final int totalRouteInstances;
  final int completedInstances;
  final double visitCompletionRate;
  final int totalVisits;
  final int completedVisits;
  final int skippedVisits;
  final double avgVisitDuration;

  factory RouteKpi.fromJson(Map<String, dynamic> json) {
    return RouteKpi(
      completionRate: (json['completion_rate'] as num?)?.toDouble() ?? 0,
      totalRouteInstances: json['total_route_instances'] as int? ?? 0,
      completedInstances: json['completed_instances'] as int? ?? 0,
      visitCompletionRate:
          (json['visit_completion_rate'] as num?)?.toDouble() ?? 0,
      totalVisits: json['total_visits'] as int? ?? 0,
      completedVisits: json['completed_visits'] as int? ?? 0,
      skippedVisits: json['skipped_visits'] as int? ?? 0,
      avgVisitDuration: (json['avg_visit_duration'] as num?)?.toDouble() ?? 0,
    );
  }
}

class CampaignKpi {
  const CampaignKpi({
    required this.totalBudget,
    required this.totalSpent,
    required this.budgetUtilization,
  });

  final double totalBudget;
  final double totalSpent;
  final double budgetUtilization;

  factory CampaignKpi.fromJson(Map<String, dynamic> json) {
    return CampaignKpi(
      totalBudget: (json['total_budget'] as num?)?.toDouble() ?? 0,
      totalSpent: (json['total_spent'] as num?)?.toDouble() ?? 0,
      budgetUtilization:
          (json['budget_utilization'] as num?)?.toDouble() ?? 0,
    );
  }
}

class RankedItem {
  const RankedItem({
    required this.name,
    required this.totalSales,
    required this.orderCount,
  });

  final String name;
  final double totalSales;
  final int orderCount;

  factory RankedItem.fromJson(Map<String, dynamic> json) {
    return RankedItem(
      name: (json['store_name'] ?? json['name'] ?? 'N/A') as String,
      totalSales: (json['total_sales'] as num?)?.toDouble() ?? 0,
      orderCount: json['order_count'] as int? ?? 0,
    );
  }
}
