import '../../../../core/enums/pos_transaction_status.dart';
import '../../../../core/enums/pos_transaction_type.dart';
import 'pos_transaction_item_model.dart';

class PosTransaction {
  const PosTransaction({
    required this.id,
    required this.transactionNumber,
    required this.type,
    required this.status,
    required this.totalAmount,
    this.storeName,
    this.storeId,
    this.terminalName,
    this.userName,
    this.subtotal,
    this.taxAmount,
    this.paymentMethod,
    this.notes,
    this.syncedAt,
    this.createdAt,
    this.items,
  });

  factory PosTransaction.fromJson(Map<String, dynamic> json) {
    final store = json['store'] as Map<String, dynamic>?;
    final terminal = json['terminal'] as Map<String, dynamic>?;
    final user = json['user'] as Map<String, dynamic>?;
    final itemsList = json['items'] as List?;
    final typeData = json['type'];
    final statusData = json['status'];

    return PosTransaction(
      id: json['id'] as int,
      transactionNumber: json['transaction_number'] as String,
      type: PosTransactionType.fromString(
        typeData is Map ? typeData['value'] as String : typeData as String? ?? 'sell_out',
      ),
      status: PosTransactionStatus.fromString(
        statusData is Map ? statusData['value'] as String : statusData as String? ?? 'pending',
      ),
      storeId: store?['id'] as int?,
      storeName: store?['name'] as String?,
      terminalName: terminal?['name'] as String?,
      userName: user?['name'] as String?,
      subtotal: (json['subtotal'] as num?)?.toDouble(),
      taxAmount: (json['tax_amount'] as num?)?.toDouble(),
      totalAmount: (json['total_amount'] as num).toDouble(),
      paymentMethod: json['payment_method'] as String?,
      notes: json['notes'] as String?,
      syncedAt: json['synced_at'] as String?,
      createdAt: json['created_at'] as String?,
      items: itemsList
          ?.map((e) => PosTransactionItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  final int id;
  final String transactionNumber;
  final PosTransactionType type;
  final PosTransactionStatus status;
  final int? storeId;
  final String? storeName;
  final String? terminalName;
  final String? userName;
  final double? subtotal;
  final double? taxAmount;
  final double totalAmount;
  final String? paymentMethod;
  final String? notes;
  final String? syncedAt;
  final String? createdAt;
  final List<PosTransactionItem>? items;
}
