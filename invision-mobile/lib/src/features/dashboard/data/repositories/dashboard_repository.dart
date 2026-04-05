import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/dashboard_models.dart';
import '../models/inquiry_models.dart';

class DashboardRepository {
  DashboardRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  Future<OverviewKpi> getOverview() async {
    final response = await _client.dio.get(ApiEndpoints.dashboardOverview);
    return OverviewKpi.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  Future<SalesKpi> getSalesKpi({String period = 'month'}) async {
    final response = await _client.dio.get(
      ApiEndpoints.dashboardSales,
      queryParameters: {'period': period},
    );
    return SalesKpi.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  Future<RouteKpi> getRouteKpi({String period = 'month'}) async {
    final response = await _client.dio.get(
      ApiEndpoints.dashboardRoutes,
      queryParameters: {'period': period},
    );
    return RouteKpi.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  Future<CampaignKpi> getCampaignKpi() async {
    final response = await _client.dio.get(ApiEndpoints.dashboardCampaigns);
    return CampaignKpi.fromJson(
        response.data['data'] as Map<String, dynamic>);
  }

  Future<List<StoreInquiryItem>> getStoreInquiry({
    String? search,
    String? category,
    String? rank,
  }) async {
    final params = <String, dynamic>{};
    if (search != null && search.isNotEmpty) params['search'] = search;
    if (category != null && category.isNotEmpty) params['category'] = category;
    if (rank != null && rank.isNotEmpty) params['rank'] = rank;

    final response = await _client.dio.get(
      ApiEndpoints.inquiryStores,
      queryParameters: params,
    );
    final data = response.data['data'] as List;
    return data
        .map((e) => StoreInquiryItem.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<List<SalesInquiryItem>> getSalesInquiry({
    String? status,
    String? search,
    String? dateFrom,
    String? dateTo,
  }) async {
    final params = <String, dynamic>{};
    if (status != null && status.isNotEmpty) params['status'] = status;
    if (search != null && search.isNotEmpty) params['search'] = search;
    if (dateFrom != null && dateFrom.isNotEmpty) params['date_from'] = dateFrom;
    if (dateTo != null && dateTo.isNotEmpty) params['date_to'] = dateTo;

    final response = await _client.dio.get(
      ApiEndpoints.inquirySales,
      queryParameters: params,
    );
    final data = response.data['data'] as List;
    return data
        .map((e) => SalesInquiryItem.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<List<RouteInquiryItem>> getRouteInquiry({
    String? status,
    String? dateFrom,
    String? dateTo,
  }) async {
    final params = <String, dynamic>{};
    if (status != null && status.isNotEmpty) params['status'] = status;
    if (dateFrom != null && dateFrom.isNotEmpty) params['date_from'] = dateFrom;
    if (dateTo != null && dateTo.isNotEmpty) params['date_to'] = dateTo;

    final response = await _client.dio.get(
      ApiEndpoints.inquiryRoutes,
      queryParameters: params,
    );
    final data = response.data['data'] as List;
    return data
        .map((e) => RouteInquiryItem.fromJson(e as Map<String, dynamic>))
        .toList();
  }
}
