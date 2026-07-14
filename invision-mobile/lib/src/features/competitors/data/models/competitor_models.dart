class Competitor {
  const Competitor({
    required this.id,
    required this.name,
    this.description,
    this.logoPath,
    this.isActive = true,
    this.productsCount,
    this.observationsCount,
  });

  factory Competitor.fromJson(Map<String, dynamic> json) {
    return Competitor(
      id: json['id'] as int,
      name: json['name'] as String,
      description: json['description'] as String?,
      logoPath: json['logo_path'] as String?,
      isActive: json['is_active'] as bool? ?? true,
      productsCount: json['products_count'] as int?,
      observationsCount: json['observations_count'] as int?,
    );
  }

  final int id;
  final String name;
  final String? description;
  final String? logoPath;
  final bool isActive;
  final int? productsCount;
  final int? observationsCount;
}

class CompetitorProduct {
  const CompetitorProduct({
    required this.id,
    required this.competitorId,
    required this.name,
    this.sku,
    this.barcode,
    this.category,
    this.description,
    this.imagePath,
    this.isActive = true,
    this.competitorName,
  });

  factory CompetitorProduct.fromJson(Map<String, dynamic> json) {
    return CompetitorProduct(
      id: json['id'] as int,
      competitorId: json['competitor_id'] as int,
      name: json['name'] as String,
      sku: json['sku'] as String?,
      barcode: json['barcode'] as String?,
      category: json['category'] as String?,
      description: json['description'] as String?,
      imagePath: json['image_path'] as String?,
      isActive: json['is_active'] as bool? ?? true,
      competitorName: json['competitor'] != null
          ? (json['competitor'] as Map<String, dynamic>)['name'] as String?
          : null,
    );
  }

  final int id;
  final int competitorId;
  final String name;
  final String? sku;
  final String? barcode;
  final String? category;
  final String? description;
  final String? imagePath;
  final bool isActive;
  final String? competitorName;
}

class CompetitorObservation {
  const CompetitorObservation({
    required this.id,
    required this.storeId,
    required this.userId,
    required this.observationType,
    this.storeVisitId,
    this.competitorId,
    this.competitorProductId,
    this.quantity,
    this.price,
    this.notes,
    this.photoPath,
    this.latitude,
    this.longitude,
    this.observedAt,
    this.storeName,
    this.userName,
    this.competitorName,
    this.productName,
  });

  factory CompetitorObservation.fromJson(Map<String, dynamic> json) {
    return CompetitorObservation(
      id: json['id'] as int,
      storeVisitId: json['store_visit_id'] as int?,
      storeId: json['store_id'] as int,
      userId: json['user_id'] as int,
      competitorId: json['competitor_id'] as int?,
      competitorProductId: json['competitor_product_id'] as int?,
      observationType: json['observation_type'] as String,
      quantity: json['quantity'] as int?,
      price: (json['price'] as num?)?.toDouble(),
      notes: json['notes'] as String?,
      photoPath: json['photo_path'] as String?,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      observedAt: json['observed_at'] as String?,
      storeName: json['store'] != null
          ? (json['store'] as Map<String, dynamic>)['name'] as String?
          : null,
      userName: json['user'] != null
          ? '${(json['user'] as Map<String, dynamic>)['first_name']} ${(json['user'] as Map<String, dynamic>)['last_name']}'
          : null,
      competitorName: json['competitor'] != null
          ? (json['competitor'] as Map<String, dynamic>)['name'] as String?
          : null,
      productName: json['competitor_product'] != null
          ? (json['competitor_product'] as Map<String, dynamic>)['name'] as String?
          : null,
    );
  }

  final int id;
  final int? storeVisitId;
  final int storeId;
  final int userId;
  final int? competitorId;
  final int? competitorProductId;
  final String observationType;
  final int? quantity;
  final double? price;
  final String? notes;
  final String? photoPath;
  final double? latitude;
  final double? longitude;
  final String? observedAt;
  final String? storeName;
  final String? userName;
  final String? competitorName;
  final String? productName;
}

class CompetitorAnalysisItem {
  const CompetitorAnalysisItem({
    required this.competitor,
    required this.totalObservations,
    required this.types,
  });

  factory CompetitorAnalysisItem.fromJson(Map<String, dynamic> json) {
    return CompetitorAnalysisItem(
      competitor: json['competitor'] as String,
      totalObservations: json['total_observations'] as int,
      types: (json['types'] as List)
          .map((t) => AnalysisType.fromJson(t as Map<String, dynamic>))
          .toList(),
    );
  }

  final String competitor;
  final int totalObservations;
  final List<AnalysisType> types;
}

class AnalysisType {
  const AnalysisType({
    required this.type,
    required this.count,
    this.avgPrice,
    this.totalQuantity,
  });

  factory AnalysisType.fromJson(Map<String, dynamic> json) {
    return AnalysisType(
      type: json['type'] as String,
      count: json['count'] as int,
      avgPrice: (json['avg_price'] as num?)?.toDouble(),
      totalQuantity: json['total_quantity'] as int?,
    );
  }

  final String type;
  final int count;
  final double? avgPrice;
  final int? totalQuantity;
}
