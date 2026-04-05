import '../../../../core/enums/order_status.dart';
import 'sales_order_item_model.dart';

class SalesOrder {
  const SalesOrder({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.subtotal,
    required this.totalAmount,
    this.storeName,
    this.storeId,
    this.salespersonName,
    this.discountAmount,
    this.taxAmount,
    this.totalPaid,
    this.balanceDue,
    this.notes,
    this.deliveredAt,
    this.createdAt,
    this.items,
    this.payments,
  });

  factory SalesOrder.fromJson(Map<String, dynamic> json) {
    final store = json['store'] as Map<String, dynamic>?;
    final salesperson = json['salesperson'] as Map<String, dynamic>?;
    final itemsList = json['items'] as List?;
    final paymentsList = json['payments'] as List?;

    return SalesOrder(
      id: json['id'] as int,
      orderNumber: json['order_number'] as String,
      status: OrderStatus.fromString(json['status'] as String? ?? 'draft'),
      storeId: json['store_id'] as int?,
      storeName: store != null ? store['name'] as String? : null,
      salespersonName: salesperson != null
          ? '${salesperson['first_name']} ${salesperson['last_name']}'
          : null,
      subtotal: (json['subtotal'] as num).toDouble(),
      discountAmount: (json['discount_amount'] as num?)?.toDouble(),
      taxAmount: (json['tax_amount'] as num?)?.toDouble(),
      totalAmount: (json['total_amount'] as num).toDouble(),
      totalPaid: (json['total_paid'] as num?)?.toDouble(),
      balanceDue: (json['balance_due'] as num?)?.toDouble(),
      notes: json['notes'] as String?,
      deliveredAt: json['delivered_at'] as String?,
      createdAt: json['created_at'] as String?,
      items: itemsList
          ?.map((e) =>
              SalesOrderItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      payments: paymentsList
          ?.map((e) => e as Map<String, dynamic>)
          .toList(),
    );
  }

  final int id;
  final String orderNumber;
  final OrderStatus status;
  final int? storeId;
  final String? storeName;
  final String? salespersonName;
  final double subtotal;
  final double? discountAmount;
  final double? taxAmount;
  final double totalAmount;
  final double? totalPaid;
  final double? balanceDue;
  final String? notes;
  final String? deliveredAt;
  final String? createdAt;
  final List<SalesOrderItem>? items;
  final List<Map<String, dynamic>>? payments;
}
