class Product {
  const Product({
    required this.id,
    required this.name,
    required this.isActive,
    this.sku,
    this.barcode,
    this.description,
    this.categoryName,
    this.priceLevels = const [],
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'] as int,
      name: json['name'] as String,
      isActive: json['is_active'] as bool? ?? true,
      sku: json['sku'] as String?,
      barcode: json['barcode'] as String?,
      description: json['description'] as String?,
      categoryName: json['category'] != null
          ? (json['category'] as Map<String, dynamic>)['name'] as String?
          : null,
      priceLevels: json['price_levels'] != null
          ? (json['price_levels'] as List)
              .map(
                  (p) => PriceLevel.fromJson(p as Map<String, dynamic>))
              .toList()
          : [],
    );
  }

  final int id;
  final String name;
  final bool isActive;
  final String? sku;
  final String? barcode;
  final String? description;
  final String? categoryName;
  final List<PriceLevel> priceLevels;
}

class PriceLevel {
  const PriceLevel({
    required this.id,
    required this.levelName,
    required this.price,
    required this.effectiveFrom,
    this.effectiveTo,
  });

  factory PriceLevel.fromJson(Map<String, dynamic> json) {
    return PriceLevel(
      id: json['id'] as int,
      levelName: json['level_name'] as String,
      price: (json['price'] as num).toDouble(),
      effectiveFrom: json['effective_from'] as String,
      effectiveTo: json['effective_to'] as String?,
    );
  }

  final int id;
  final String levelName;
  final double price;
  final String effectiveFrom;
  final String? effectiveTo;
}
