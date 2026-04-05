class StoreInventoryItem {
  const StoreInventoryItem({
    required this.id,
    this.storeName,
    this.storeId,
    this.productName,
    this.productSku,
    this.productId,
    required this.onShelfQuantity,
    required this.warehouseQuantity,
    this.totalQuantity,
    this.lastCountedAt,
  });

  factory StoreInventoryItem.fromJson(Map<String, dynamic> json) {
    final store = json['store'] as Map<String, dynamic>?;
    final product = json['product'] as Map<String, dynamic>?;
    return StoreInventoryItem(
      id: json['id'] as int,
      storeId: store?['id'] as int?,
      storeName: store?['name'] as String?,
      productId: product?['id'] as int?,
      productName: product?['name'] as String?,
      productSku: product?['sku'] as String?,
      onShelfQuantity: json['on_shelf_quantity'] as int? ?? 0,
      warehouseQuantity: json['warehouse_quantity'] as int? ?? 0,
      totalQuantity: json['total_quantity'] as int?,
      lastCountedAt: json['last_counted_at'] as String?,
    );
  }

  final int id;
  final int? storeId;
  final String? storeName;
  final int? productId;
  final String? productName;
  final String? productSku;
  final int onShelfQuantity;
  final int warehouseQuantity;
  final int? totalQuantity;
  final String? lastCountedAt;
}
