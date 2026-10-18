class VisitCapture {
  const VisitCapture({
    required this.visitId,
    required this.storeName,
    required this.items,
    this.remarks,
  });

  factory VisitCapture.fromJson(Map<String, dynamic> json) {
    return VisitCapture(
      visitId: json['visit_id'] as int,
      storeName: json['store'] as String? ?? 'Store visit',
      remarks: json['remarks'] as String?,
      items: (json['items'] as List? ?? const [])
          .map(
            (item) => VisitCaptureMetric.fromJson(item as Map<String, dynamic>),
          )
          .toList(),
    );
  }

  final int visitId;
  final String storeName;
  final String? remarks;
  final List<VisitCaptureMetric> items;
}

class VisitCaptureMetric {
  const VisitCaptureMetric({
    required this.productId,
    required this.ourSellOutQuantity,
    required this.competitorSellOutQuantity,
    required this.ourShelfStockQuantity,
    required this.competitorShelfStockQuantity,
    required this.ourWarehouseStockQuantity,
    required this.competitorWarehouseStockQuantity,
    required this.ourSellThroughQuantity,
    required this.competitorSellThroughQuantity,
    required this.plannedSellThroughQuantity,
    this.plannedSellThroughDate,
    this.plannedSellThroughBasis,
    this.plannedSellThroughNotes,
    required this.ourPosmPresent,
    required this.competitorPosmPresent,
    required this.ourFrontShopPresent,
    required this.competitorFrontShopPresent,
    this.competitorId,
  });

  factory VisitCaptureMetric.fromJson(Map<String, dynamic> json) {
    int quantity(String key) => (json[key] as num?)?.toInt() ?? 0;
    return VisitCaptureMetric(
      productId: json['product_id'] as int,
      competitorId: json['competitor_id'] as int?,
      ourSellOutQuantity: quantity('our_sell_out_quantity'),
      competitorSellOutQuantity: quantity('competitor_sell_out_quantity'),
      ourShelfStockQuantity: quantity('our_shelf_stock_quantity'),
      competitorShelfStockQuantity: quantity('competitor_shelf_stock_quantity'),
      ourWarehouseStockQuantity: quantity('our_warehouse_stock_quantity'),
      competitorWarehouseStockQuantity: quantity(
        'competitor_warehouse_stock_quantity',
      ),
      ourSellThroughQuantity: quantity('our_sell_through_quantity'),
      competitorSellThroughQuantity: quantity(
        'competitor_sell_through_quantity',
      ),
      plannedSellThroughQuantity: quantity('planned_sell_through_quantity'),
      plannedSellThroughDate: json['planned_sell_through_date'] as String?,
      plannedSellThroughBasis: json['planned_sell_through_basis'] as String?,
      plannedSellThroughNotes: json['planned_sell_through_notes'] as String?,
      ourPosmPresent: json['our_posm_present'] as bool? ?? false,
      competitorPosmPresent: json['competitor_posm_present'] as bool? ?? false,
      ourFrontShopPresent: json['our_front_shop_present'] as bool? ?? false,
      competitorFrontShopPresent:
          json['competitor_front_shop_present'] as bool? ?? false,
    );
  }

  final int productId;
  final int? competitorId;
  final int ourSellOutQuantity;
  final int competitorSellOutQuantity;
  final int ourShelfStockQuantity;
  final int competitorShelfStockQuantity;
  final int ourWarehouseStockQuantity;
  final int competitorWarehouseStockQuantity;
  final int ourSellThroughQuantity;
  final int competitorSellThroughQuantity;
  final int plannedSellThroughQuantity;
  final String? plannedSellThroughDate;
  final String? plannedSellThroughBasis;
  final String? plannedSellThroughNotes;
  final bool ourPosmPresent;
  final bool competitorPosmPresent;
  final bool ourFrontShopPresent;
  final bool competitorFrontShopPresent;
}
