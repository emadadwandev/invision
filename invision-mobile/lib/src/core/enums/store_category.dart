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

  String get apiValue => switch (this) {
        supermarket => 'supermarket',
        hypermarket => 'hypermarket',
        miniMarket => 'mini_market',
        pharmacy => 'pharmacy',
        convenienceStore => 'convenience_store',
        wholesaleStore => 'wholesale_store',
        kiosk => 'kiosk',
        other => 'other',
      };

  static StoreCategory fromString(String value) => switch (value) {
        'supermarket' => StoreCategory.supermarket,
        'hypermarket' => StoreCategory.hypermarket,
        'mini_market' || 'miniMarket' => StoreCategory.miniMarket,
        'pharmacy' => StoreCategory.pharmacy,
        'convenience_store' || 'convenienceStore' =>
          StoreCategory.convenienceStore,
        'wholesale_store' || 'wholesaleStore' =>
          StoreCategory.wholesaleStore,
        'kiosk' => StoreCategory.kiosk,
        _ => StoreCategory.other,
      };
}
