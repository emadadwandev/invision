class StoreMapItem {
  final int id;
  final String name;
  final String code;
  final String? category;
  final String? rank;
  final String? address;
  final double latitude;
  final double longitude;
  final StoreSalesSummary sales;
  final StoreInventorySummary inventory;
  final StoreCreditSummary? credit;

  const StoreMapItem({
    required this.id,
    required this.name,
    required this.code,
    this.category,
    this.rank,
    this.address,
    required this.latitude,
    required this.longitude,
    required this.sales,
    required this.inventory,
    this.credit,
  });

  factory StoreMapItem.fromJson(Map<String, dynamic> json) {
    return StoreMapItem(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      code: json['code'] as String? ?? '',
      category: json['category'] as String?,
      rank: json['rank'] as String?,
      address: json['address'] as String?,
      latitude: (json['latitude'] as num).toDouble(),
      longitude: (json['longitude'] as num).toDouble(),
      sales: StoreSalesSummary.fromJson(
        json['sales'] as Map<String, dynamic>? ?? {},
      ),
      inventory: StoreInventorySummary.fromJson(
        json['inventory'] as Map<String, dynamic>? ?? {},
      ),
      credit: json['credit'] != null
          ? StoreCreditSummary.fromJson(json['credit'] as Map<String, dynamic>)
          : null,
    );
  }
}

class StoreSalesSummary {
  final int orderCount;
  final double totalSales;

  const StoreSalesSummary({
    required this.orderCount,
    required this.totalSales,
  });

  factory StoreSalesSummary.fromJson(Map<String, dynamic> json) {
    return StoreSalesSummary(
      orderCount: json['order_count'] as int? ?? 0,
      totalSales: (json['total_sales'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class StoreInventorySummary {
  final int productCount;
  final int totalStock;

  const StoreInventorySummary({
    required this.productCount,
    required this.totalStock,
  });

  factory StoreInventorySummary.fromJson(Map<String, dynamic> json) {
    return StoreInventorySummary(
      productCount: json['product_count'] as int? ?? 0,
      totalStock: json['total_stock'] as int? ?? 0,
    );
  }
}

class StoreCreditSummary {
  final double creditLimit;
  final double currentBalance;
  final double availableCredit;

  const StoreCreditSummary({
    required this.creditLimit,
    required this.currentBalance,
    required this.availableCredit,
  });

  factory StoreCreditSummary.fromJson(Map<String, dynamic> json) {
    return StoreCreditSummary(
      creditLimit: (json['credit_limit'] as num?)?.toDouble() ?? 0.0,
      currentBalance: (json['current_balance'] as num?)?.toDouble() ?? 0.0,
      availableCredit: (json['available_credit'] as num?)?.toDouble() ?? 0.0,
    );
  }
}
