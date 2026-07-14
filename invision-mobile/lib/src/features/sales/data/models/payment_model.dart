import '../../../../core/enums/payment_method.dart';
import '../../../../core/enums/payment_status.dart';

class Payment {
  const Payment({
    required this.id,
    required this.paymentMethod,
    required this.amount,
    required this.status,
    this.salesOrderId,
    this.orderNumber,
    this.collectorName,
    this.referenceNumber,
    this.checkNumber,
    this.checkDate,
    this.bankName,
    this.paidAt,
    this.notes,
  });

  factory Payment.fromJson(Map<String, dynamic> json) {
    final salesOrder = json['sales_order'] as Map<String, dynamic>?;
    final collector = json['collector'] as Map<String, dynamic>?;

    return Payment(
      id: json['id'] as int,
      salesOrderId: json['sales_order_id'] as int?,
      orderNumber: salesOrder != null
          ? salesOrder['order_number'] as String?
          : null,
      paymentMethod: PaymentMethod.fromString(
          json['payment_method'] as String? ?? 'cash'),
      amount: (json['amount'] as num).toDouble(),
      status: PaymentStatus.fromString(
          json['status'] as String? ?? 'pending'),
      collectorName: collector != null
          ? '${collector['first_name']} ${collector['last_name']}'
          : null,
      referenceNumber: json['reference_number'] as String?,
      checkNumber: json['check_number'] as String?,
      checkDate: json['check_date'] as String?,
      bankName: json['bank_name'] as String?,
      paidAt: json['paid_at'] as String?,
      notes: json['notes'] as String?,
    );
  }

  final int id;
  final int? salesOrderId;
  final String? orderNumber;
  final PaymentMethod paymentMethod;
  final double amount;
  final PaymentStatus status;
  final String? collectorName;
  final String? referenceNumber;
  final String? checkNumber;
  final String? checkDate;
  final String? bankName;
  final String? paidAt;
  final String? notes;
}
