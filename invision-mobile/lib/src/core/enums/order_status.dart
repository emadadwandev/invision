enum OrderStatus {
  draft,
  confirmed,
  delivered,
  cancelled,
  returned;

  String get label => switch (this) {
    draft => 'Draft',
    confirmed => 'Confirmed',
    delivered => 'Delivered',
    cancelled => 'Cancelled',
    returned => 'Returned',
  };

  static OrderStatus fromString(String value) {
    return switch (value) {
      'draft' => OrderStatus.draft,
      'confirmed' => OrderStatus.confirmed,
      'delivered' => OrderStatus.delivered,
      'cancelled' => OrderStatus.cancelled,
      'returned' => OrderStatus.returned,
      _ => OrderStatus.draft,
    };
  }
}
