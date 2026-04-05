enum PaymentStatus {
  pending,
  paid,
  partiallyPaid,
  overdue,
  refunded;

  String get label => switch (this) {
    pending => 'Pending',
    paid => 'Paid',
    partiallyPaid => 'Partially Paid',
    overdue => 'Overdue',
    refunded => 'Refunded',
  };

  static PaymentStatus fromString(String value) {
    return switch (value) {
      'pending' => PaymentStatus.pending,
      'paid' => PaymentStatus.paid,
      'partially_paid' => PaymentStatus.partiallyPaid,
      'overdue' => PaymentStatus.overdue,
      'refunded' => PaymentStatus.refunded,
      _ => PaymentStatus.pending,
    };
  }
}
