import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/credit_account_model.dart';
import '../models/payment_model.dart';
import '../models/sales_order_model.dart';

class SalesRepository {
  SalesRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  // Sales Orders
  Future<List<SalesOrder>> getSalesOrders({
    String? search,
    String? status,
  }) async {
    final queryParams = <String, dynamic>{};
    if (search != null && search.isNotEmpty) queryParams['search'] = search;
    if (status != null && status.isNotEmpty) queryParams['status'] = status;

    final response = await _client.dio.get(
      ApiEndpoints.salesOrders,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => SalesOrder.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<SalesOrder> getSalesOrder(int id) async {
    final response = await _client.dio.get(ApiEndpoints.salesOrder(id));
    return SalesOrder.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<SalesOrder> createSalesOrder({
    required int storeId,
    required List<Map<String, dynamic>> items,
    String? notes,
  }) async {
    final response = await _client.dio.post(
      ApiEndpoints.salesOrders,
      data: {
        'store_id': storeId,
        'items': items,
        if (notes != null) 'notes': notes,
      },
    );
    return SalesOrder.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<SalesOrder> confirmOrder(int id) async {
    final response =
        await _client.dio.post(ApiEndpoints.confirmOrder(id));
    return SalesOrder.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<SalesOrder> deliverOrder(int id) async {
    final response =
        await _client.dio.post(ApiEndpoints.deliverOrder(id));
    return SalesOrder.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<SalesOrder> cancelOrder(int id) async {
    final response =
        await _client.dio.post(ApiEndpoints.cancelOrder(id));
    return SalesOrder.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  // My Orders (Mobile)
  Future<List<SalesOrder>> getMyOrders({String? status}) async {
    final queryParams = <String, dynamic>{};
    if (status != null && status.isNotEmpty) queryParams['status'] = status;

    final response = await _client.dio.get(
      ApiEndpoints.myOrders,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => SalesOrder.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  // Payments
  Future<List<Payment>> getPayments({
    String? status,
    String? method,
  }) async {
    final queryParams = <String, dynamic>{};
    if (status != null && status.isNotEmpty) queryParams['status'] = status;
    if (method != null && method.isNotEmpty) queryParams['method'] = method;

    final response = await _client.dio.get(
      ApiEndpoints.payments,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) => Payment.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<Payment> recordPayment({
    required int salesOrderId,
    required String paymentMethod,
    required double amount,
    String? checkNumber,
    String? checkDate,
    String? bankName,
    String? notes,
  }) async {
    final response = await _client.dio.post(
      ApiEndpoints.payments,
      data: {
        'sales_order_id': salesOrderId,
        'payment_method': paymentMethod,
        'amount': amount,
        if (checkNumber != null) 'check_number': checkNumber,
        if (checkDate != null) 'check_date': checkDate,
        if (bankName != null) 'bank_name': bankName,
        if (notes != null) 'notes': notes,
      },
    );
    return Payment.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  // Credit Accounts
  Future<List<CreditAccount>> getCreditAccounts({String? search}) async {
    final queryParams = <String, dynamic>{};
    if (search != null && search.isNotEmpty) queryParams['search'] = search;

    final response = await _client.dio.get(
      ApiEndpoints.creditAccounts,
      queryParameters: queryParams,
    );
    final data = response.data['data'] as List;
    return data
        .map((json) =>
            CreditAccount.fromJson(json as Map<String, dynamic>))
        .toList();
  }
}
