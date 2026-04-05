class StoreInquiryItem {
  const StoreInquiryItem({
    required this.id,
    required this.name,
    required this.code,
    this.category,
    this.rank,
    this.area,
    required this.orderCount,
    required this.totalSales,
    required this.stockQuantity,
    this.creditLimit,
    this.creditBalance,
  });

  final int id;
  final String name;
  final String code;
  final String? category;
  final String? rank;
  final String? area;
  final int orderCount;
  final double totalSales;
  final int stockQuantity;
  final double? creditLimit;
  final double? creditBalance;

  factory StoreInquiryItem.fromJson(Map<String, dynamic> json) {
    return StoreInquiryItem(
      id: json['id'] as int,
      name: json['name'] as String,
      code: json['code'] as String,
      category: json['category'] as String?,
      rank: json['rank'] as String?,
      area: json['area'] as String?,
      orderCount: json['order_count'] as int? ?? 0,
      totalSales: (json['total_sales'] as num?)?.toDouble() ?? 0,
      stockQuantity: json['stock_quantity'] as int? ?? 0,
      creditLimit: (json['credit_limit'] as num?)?.toDouble(),
      creditBalance: (json['credit_balance'] as num?)?.toDouble(),
    );
  }
}

class SalesInquiryItem {
  const SalesInquiryItem({
    required this.id,
    required this.orderNumber,
    this.storeName,
    this.salesperson,
    required this.status,
    required this.total,
    required this.paid,
    required this.balanceDue,
    required this.createdAt,
  });

  final int id;
  final String orderNumber;
  final String? storeName;
  final String? salesperson;
  final String status;
  final double total;
  final double paid;
  final double balanceDue;
  final String createdAt;

  factory SalesInquiryItem.fromJson(Map<String, dynamic> json) {
    return SalesInquiryItem(
      id: json['id'] as int,
      orderNumber: json['order_number'] as String,
      storeName: json['store_name'] as String?,
      salesperson: json['salesperson'] as String?,
      status: json['status'] as String,
      total: (json['total'] as num?)?.toDouble() ?? 0,
      paid: (json['paid'] as num?)?.toDouble() ?? 0,
      balanceDue: (json['balance_due'] as num?)?.toDouble() ?? 0,
      createdAt: json['created_at'] as String,
    );
  }
}

class RouteInquiryItem {
  const RouteInquiryItem({
    required this.id,
    this.routeName,
    this.user,
    required this.status,
    required this.routeDate,
    required this.totalVisits,
    required this.completedVisits,
    required this.completionPct,
    this.distanceKm,
  });

  final int id;
  final String? routeName;
  final String? user;
  final String status;
  final String routeDate;
  final int totalVisits;
  final int completedVisits;
  final double completionPct;
  final double? distanceKm;

  factory RouteInquiryItem.fromJson(Map<String, dynamic> json) {
    return RouteInquiryItem(
      id: json['id'] as int,
      routeName: json['route_name'] as String?,
      user: json['user'] as String?,
      status: json['status'] as String,
      routeDate: json['route_date'] as String? ?? '',
      totalVisits: json['total_visits'] as int? ?? 0,
      completedVisits: json['completed_visits'] as int? ?? 0,
      completionPct: (json['completion_pct'] as num?)?.toDouble() ?? 0,
      distanceKm: (json['distance_km'] as num?)?.toDouble(),
    );
  }
}
