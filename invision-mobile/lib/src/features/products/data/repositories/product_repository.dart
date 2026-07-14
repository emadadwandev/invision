import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/product_category_model.dart';
import '../models/product_model.dart';

class ProductRepository {
  ProductRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  Future<List<Product>> getProducts({
    String? search,
    int? categoryId,
  }) async {
    final queryParams = <String, dynamic>{};
    if (search != null && search.isNotEmpty) queryParams['search'] = search;
    if (categoryId != null) queryParams['category_id'] = categoryId;

    final response = await _client.dio.get(
      ApiEndpoints.products,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => Product.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<Product> getProduct(int id) async {
    final response = await _client.dio.get(ApiEndpoints.product(id));
    return Product.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  Future<List<ProductCategory>> getCategories() async {
    final response = await _client.dio.get(ApiEndpoints.productCategories);
    final data = response.data['data'] as List;
    return data
        .map((json) =>
            ProductCategory.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<List<ProductCategory>> getCategoryTree() async {
    final response = await _client.dio.get(ApiEndpoints.productCategoryTree);
    final data = response.data['data'] as List;
    return data
        .map((json) =>
            ProductCategory.fromJson(json as Map<String, dynamic>))
        .toList();
  }
}
