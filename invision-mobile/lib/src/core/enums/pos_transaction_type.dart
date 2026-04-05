enum PosTransactionType {
  sellOut,
  sellThrough,
  posReturn;

  static PosTransactionType fromString(String value) {
    return switch (value) {
      'sell_out' => PosTransactionType.sellOut,
      'sell_through' => PosTransactionType.sellThrough,
      'return' => PosTransactionType.posReturn,
      _ => PosTransactionType.sellOut,
    };
  }

  String get value => switch (this) {
    PosTransactionType.sellOut => 'sell_out',
    PosTransactionType.sellThrough => 'sell_through',
    PosTransactionType.posReturn => 'return',
  };

  String get label => switch (this) {
    PosTransactionType.sellOut => 'Sell Out',
    PosTransactionType.sellThrough => 'Sell Through',
    PosTransactionType.posReturn => 'Return',
  };
}
