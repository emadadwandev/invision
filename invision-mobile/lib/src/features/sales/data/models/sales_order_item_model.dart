class SalesOrderItem {
  const SalesOrderItem({
    required this.id,
    required this.productId,
    required this.quantity,
    required this.unitPrice,
    required this.lineTotal,
    this.productName,
    this.discountPercent,
    this.discountAmount,
    this.barcodeScanned,
  });

  factory SalesOrderItem.fromJson(Map<String, dynamic> json) {
    final product = json['product'] as Map<String, dynamic>?;
    return SalesOrderItem(
      id: json['id'] as int,
      productId: json['product_id'] as int,
      productName: product != null ? product['name'] as String? : null,
      quantity: (json['quantity'] as num).toInt(),
      unitPrice: (json['unit_price'] as num).toDouble(),
      discountPercent: (json['discount_percent'] as num?)?.toDouble(),
      discountAmount: (json['discount_amount'] as num?)?.toDouble(),
      lineTotal: (json['line_total'] as num).toDouble(),
      barcodeScanned: json['barcode_scanned'] as String?,
    );
  }

  final int id;
  final int productId;
  final String? productName;
  final int quantity;
  final double unitPrice;
  final double? discountPercent;
  final double? discountAmount;
  final double lineTotal;
  final String? barcodeScanned;
}
