class CreditAccount {
  const CreditAccount({
    required this.id,
    required this.creditLimit,
    required this.currentBalance,
    required this.availableCredit,
    this.storeName,
    this.storeId,
    this.lastPaymentAt,
  });

  factory CreditAccount.fromJson(Map<String, dynamic> json) {
    final store = json['store'] as Map<String, dynamic>?;
    return CreditAccount(
      id: json['id'] as int,
      storeId: json['store_id'] as int?,
      storeName: store != null ? store['name'] as String? : null,
      creditLimit: (json['credit_limit'] as num).toDouble(),
      currentBalance: (json['current_balance'] as num).toDouble(),
      availableCredit: (json['available_credit'] as num).toDouble(),
      lastPaymentAt: json['last_payment_at'] as String?,
    );
  }

  final int id;
  final int? storeId;
  final String? storeName;
  final double creditLimit;
  final double currentBalance;
  final double availableCredit;
  final String? lastPaymentAt;
}
