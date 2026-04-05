import '../../../../core/constants/app_constants.dart';
import '../../../../core/network/api_client.dart';
import '../models/report_models.dart';

class ReportRepository {
  ReportRepository({ApiClient? apiClient})
      : _client = apiClient ?? ApiClient();

  final ApiClient _client;

  /// Fetch a fixed report by type slug.
  Future<ReportData> getFixedReport(
    String slug, {
    String? dateFrom,
    String? dateTo,
  }) async {
    final endpoint = _endpointForSlug(slug);
    final params = <String, dynamic>{};
    if (dateFrom != null) params['date_from'] = dateFrom;
    if (dateTo != null) params['date_to'] = dateTo;

    final response = await _client.dio.get(endpoint, queryParameters: params);
    return ReportData.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  /// Get available entities for the dynamic report builder.
  Future<List<ReportEntity>> getEntities() async {
    final response = await _client.dio.get(ApiEndpoints.reportEntities);
    final map = response.data['data'] as Map<String, dynamic>;
    return map.entries
        .map((e) => ReportEntity.fromJson(e.key, e.value as Map<String, dynamic>))
        .toList();
  }

  /// Build and run a dynamic report.
  Future<ReportData> buildReport({
    required String entity,
    String? groupBy,
    String? orderBy,
    String orderDir = 'desc',
    int limit = 100,
    Map<String, dynamic>? filters,
  }) async {
    final response = await _client.dio.post(
      ApiEndpoints.reportBuild,
      data: {
        'entity': entity,
        if (groupBy case final g?) 'group_by': g,
        if (orderBy case final o?) 'order_by': o,
        'order_dir': orderDir,
        'limit': limit,
        if (filters case final f?) 'filters': f,
      },
    );
    return ReportData.fromJson(response.data['data'] as Map<String, dynamic>);
  }

  /// Get the export URL for Excel.
  String getExcelExportUrl(String reportType, {String? dateFrom, String? dateTo}) {
    final base = '${AppConstants.apiBaseUrl}${ApiEndpoints.reportExportExcel}';
    final params = ['report_type=$reportType'];
    if (dateFrom != null) params.add('date_from=$dateFrom');
    if (dateTo != null) params.add('date_to=$dateTo');
    return '$base?${params.join('&')}';
  }

  /// Get the export URL for PDF.
  String getPdfExportUrl(String reportType, {String? dateFrom, String? dateTo}) {
    final base = '${AppConstants.apiBaseUrl}${ApiEndpoints.reportExportPdf}';
    final params = ['report_type=$reportType'];
    if (dateFrom != null) params.add('date_from=$dateFrom');
    if (dateTo != null) params.add('date_to=$dateTo');
    return '$base?${params.join('&')}';
  }

  String _endpointForSlug(String slug) {
    return switch (slug) {
      'sell-through' => ApiEndpoints.reportSellThrough,
      'sell-out' => ApiEndpoints.reportSellOut,
      'sell-in' => ApiEndpoints.reportSellIn,
      'stock-movement' => ApiEndpoints.reportStockMovement,
      'vendor-ranking' => ApiEndpoints.reportVendorRanking,
      'sales-rep-performance' => ApiEndpoints.reportSalesRepPerformance,
      _ => ApiEndpoints.reportSellThrough,
    };
  }
}
