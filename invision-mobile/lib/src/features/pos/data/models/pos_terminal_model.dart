class PosTerminal {
  const PosTerminal({
    required this.id,
    required this.terminalCode,
    required this.name,
    this.storeName,
    this.storeId,
    required this.isActive,
    this.lastSyncedAt,
    this.createdAt,
  });

  factory PosTerminal.fromJson(Map<String, dynamic> json) {
    final store = json['store'] as Map<String, dynamic>?;
    return PosTerminal(
      id: json['id'] as int,
      terminalCode: json['terminal_code'] as String,
      name: json['name'] as String,
      storeId: store?['id'] as int?,
      storeName: store?['name'] as String?,
      isActive: json['is_active'] as bool? ?? true,
      lastSyncedAt: json['last_synced_at'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }

  final int id;
  final String terminalCode;
  final String name;
  final int? storeId;
  final String? storeName;
  final bool isActive;
  final String? lastSyncedAt;
  final String? createdAt;
}
