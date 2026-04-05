import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/network/api_client.dart';
import '../../data/models/product_category_model.dart';
import '../../data/models/product_model.dart';
import '../../data/repositories/product_repository.dart';

final productRepositoryProvider = Provider(
  (ref) => ProductRepository(apiClient: ref.watch(apiClientProvider)),
);

final productsProvider =
    FutureProvider.autoDispose.family<List<Product>, ProductFilter>(
  (ref, filter) {
    final repo = ref.watch(productRepositoryProvider);
    return repo.getProducts(
      search: filter.search,
      categoryId: filter.categoryId,
    );
  },
);

final productDetailProvider = FutureProvider.autoDispose.family<Product, int>(
  (ref, id) {
    final repo = ref.watch(productRepositoryProvider);
    return repo.getProduct(id);
  },
);

final productCategoriesProvider =
    FutureProvider.autoDispose<List<ProductCategory>>(
  (ref) {
    final repo = ref.watch(productRepositoryProvider);
    return repo.getCategories();
  },
);

class ProductFilter {
  const ProductFilter({this.search, this.categoryId});

  final String? search;
  final int? categoryId;

  ProductFilter copyWith({String? search, int? categoryId}) {
    return ProductFilter(
      search: search ?? this.search,
      categoryId: categoryId ?? this.categoryId,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is ProductFilter &&
          search == other.search &&
          categoryId == other.categoryId;

  @override
  int get hashCode => Object.hash(search, categoryId);
}
