enum StoreCategory {
  supermarket,
  hypermarket,
  miniMarket,
  pharmacy,
  convenienceStore,
  wholesaleStore,
  kiosk,
  other;

  String get label => switch (this) {
        supermarket => 'Supermarket',
        hypermarket => 'Hypermarket',
        miniMarket => 'Mini Market',
        pharmacy => 'Pharmacy',
        convenienceStore => 'Convenience Store',
        wholesaleStore => 'Wholesale Store',
        kiosk => 'Kiosk',
        other => 'Other',
      };

  static StoreCategory fromString(String value) =>
      StoreCategory.values.firstWhere(
        (e) => e.name == value,
        orElse: () => StoreCategory.other,
      );
}
