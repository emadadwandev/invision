import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/store_model.dart';

class StoreRepository {
  StoreRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  Future<List<Store>> getStores({
    String? search,
    String? category,
    String? rank,
  }) async {
    final queryParams = <String, dynamic>{};
    if (search != null && search.isNotEmpty) queryParams['search'] = search;
    if (category != null && category.isNotEmpty) {
      queryParams['category'] = category;
    }
    if (rank != null && rank.isNotEmpty) queryParams['rank'] = rank;

    final response = await _client.dio.get(
      ApiEndpoints.stores,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => Store.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<Store> getStore(int id) async {
    final response = await _client.dio.get(ApiEndpoints.store(id));
    return Store.fromJson(response.data['data'] as Map<String, dynamic>);
  }
}
