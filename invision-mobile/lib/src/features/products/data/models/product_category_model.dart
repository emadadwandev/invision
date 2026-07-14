class ProductCategory {
  const ProductCategory({
    required this.id,
    required this.name,
    required this.isActive,
    this.parentId,
    this.parentName,
    this.sortOrder = 0,
    this.children = const [],
  });

  factory ProductCategory.fromJson(Map<String, dynamic> json) {
    return ProductCategory(
      id: json['id'] as int,
      name: json['name'] as String,
      isActive: json['is_active'] as bool? ?? true,
      parentId: json['parent_id'] as int?,
      parentName: json['parent'] != null
          ? (json['parent'] as Map<String, dynamic>)['name'] as String?
          : null,
      sortOrder: json['sort_order'] as int? ?? 0,
      children: json['children'] != null
          ? (json['children'] as List)
              .map((c) =>
                  ProductCategory.fromJson(c as Map<String, dynamic>))
              .toList()
          : [],
    );
  }

  final int id;
  final String name;
  final bool isActive;
  final int? parentId;
  final String? parentName;
  final int sortOrder;
  final List<ProductCategory> children;
}
