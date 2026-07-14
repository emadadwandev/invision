class CommandCenterStats {
  final int totalFieldForce;
  final int onlineCount;
  final int activeRoutes;
  final int totalStores;
  final int todayOrders;
  final double todaySales;

  const CommandCenterStats({
    required this.totalFieldForce,
    required this.onlineCount,
    required this.activeRoutes,
    required this.totalStores,
    required this.todayOrders,
    required this.todaySales,
  });

  factory CommandCenterStats.fromJson(Map<String, dynamic> json) {
    return CommandCenterStats(
      totalFieldForce: json['total_field_force'] as int? ?? 0,
      onlineCount: json['online_count'] as int? ?? 0,
      activeRoutes: json['active_routes'] as int? ?? 0,
      totalStores: json['total_stores'] as int? ?? 0,
      todayOrders: json['today_orders'] as int? ?? 0,
      todaySales: (json['today_sales'] as num?)?.toDouble() ?? 0.0,
    );
  }
}
