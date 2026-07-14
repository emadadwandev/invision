enum PaymentMethod {
  cash,
  check,
  creditCard,
  bankTransfer,
  credit;

  String get label => switch (this) {
    cash => 'Cash',
    check => 'Check',
    creditCard => 'Credit Card',
    bankTransfer => 'Bank Transfer',
    credit => 'Credit',
  };

  static PaymentMethod fromString(String value) {
    return switch (value) {
      'cash' => PaymentMethod.cash,
      'check' => PaymentMethod.check,
      'credit_card' => PaymentMethod.creditCard,
      'bank_transfer' => PaymentMethod.bankTransfer,
      'credit' => PaymentMethod.credit,
      _ => PaymentMethod.cash,
    };
  }
}
