class PosTransactionItem {
  const PosTransactionItem({
    required this.id,
    this.productName,
    this.productSku,
    required this.quantity,
    required this.unitPrice,
    required this.lineTotal,
    this.discountAmount,
    this.barcodeScanned,
  });

  factory PosTransactionItem.fromJson(Map<String, dynamic> json) {
    final product = json['product'] as Map<String, dynamic>?;
    return PosTransactionItem(
      id: json['id'] as int,
      productName: product?['name'] as String?,
      productSku: product?['sku'] as String?,
      quantity: json['quantity'] as int,
      unitPrice: (json['unit_price'] as num).toDouble(),
      discountAmount: (json['discount_amount'] as num?)?.toDouble(),
      lineTotal: (json['line_total'] as num).toDouble(),
      barcodeScanned: json['barcode_scanned'] as String?,
    );
  }

  final int id;
  final String? productName;
  final String? productSku;
  final int quantity;
  final double unitPrice;
  final double? discountAmount;
  final double lineTotal;
  final String? barcodeScanned;
}
