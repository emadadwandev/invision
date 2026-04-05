import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/pos_terminal_model.dart';
import '../models/pos_transaction_model.dart';
import '../models/store_inventory_model.dart';

class PosRepository {
  PosRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  // POS Transactions
  Future<List<PosTransaction>> getTransactions({
    String? search,
    String? type,
    String? status,
    int? storeId,
  }) async {
    final queryParams = <String, dynamic>{};
    if (search != null && search.isNotEmpty) queryParams['search'] = search;
    if (type != null && type.isNotEmpty) queryParams['type'] = type;
    if (status != null && status.isNotEmpty) queryParams['status'] = status;
    if (storeId != null) queryParams['store_id'] = storeId;

    final response = await _client.dio.get(
      ApiEndpoints.posTransactions,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => PosTransaction.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<PosTransaction> getTransaction(int id) async {
    final response = await _client.dio.get(ApiEndpoints.posTransaction(id));
    return PosTransaction.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<PosTransaction> createTransaction({
    required int storeId,
    int? posTerminalId,
    String? type,
    String? paymentMethod,
    String? notes,
    required List<Map<String, dynamic>> items,
  }) async {
    final response = await _client.dio.post(
      ApiEndpoints.posTransactions,
      data: {
        'store_id': storeId,
        if (posTerminalId != null) 'pos_terminal_id': posTerminalId,
        if (type != null) 'type': type,
        if (paymentMethod != null) 'payment_method': paymentMethod,
        if (notes != null) 'notes': notes,
        'items': items,
      },
    );
    return PosTransaction.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<PosTransaction> completeTransaction(int id) async {
    final response =
        await _client.dio.post(ApiEndpoints.completeTransaction(id));
    return PosTransaction.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<PosTransaction> voidTransaction(int id) async {
    final response =
        await _client.dio.post(ApiEndpoints.voidTransaction(id));
    return PosTransaction.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  // My Transactions (Mobile)
  Future<List<PosTransaction>> getMyTransactions({String? type}) async {
    final queryParams = <String, dynamic>{};
    if (type != null && type.isNotEmpty) queryParams['type'] = type;

    final response = await _client.dio.get(
      ApiEndpoints.myTransactions,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => PosTransaction.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  // POS Terminals
  Future<List<PosTerminal>> getTerminals({
    String? search,
    int? storeId,
  }) async {
    final queryParams = <String, dynamic>{};
    if (search != null && search.isNotEmpty) queryParams['search'] = search;
    if (storeId != null) queryParams['store_id'] = storeId;

    final response = await _client.dio.get(
      ApiEndpoints.posTerminals,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => PosTerminal.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  // Store Inventory
  Future<List<StoreInventoryItem>> getInventory({
    int? storeId,
    String? search,
  }) async {
    final queryParams = <String, dynamic>{};
    if (storeId != null) queryParams['store_id'] = storeId;
    if (search != null && search.isNotEmpty) queryParams['search'] = search;

    final response = await _client.dio.get(
      ApiEndpoints.storeInventory,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) =>
            StoreInventoryItem.fromJson(json as Map<String, dynamic>))
        .toList();
  }
}
